<?php

namespace App\Media\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GenerateAiTagsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $mediaId,
        public ?string $provider = null,
    ) {}

    public function handle(): void
    {
        $media = Media::findOrFail($this->mediaId);

        if (! str_starts_with($media->mime_type, 'image/')) {
            throw new \RuntimeException("Media {$media->id} is not an image file.");
        }

        $provider = $this->provider ?? config('media.ai_tagging.provider', 'clarifai');
        $apiKey = $provider === 'aws'
            ? config('services.aws.access_key_id')
            : config("services.{$provider}.api_key");

        if (empty($apiKey)) {
            \Log::warning("AI tagging skipped: API key for {$provider} is not configured.");

            return;
        }

        try {
            $tags = match ($provider) {
                'clarifai' => $this->getClarifaiTags($media, $apiKey),
                'google' => $this->getGoogleVisionTags($media, $apiKey),
                'aws' => $this->getAwsRekognitionTags($media),
                default => throw new \InvalidArgumentException("Unsupported AI provider: {$provider}"),
            };

            if (! empty($tags)) {
                $metadata = $media->metadata_json ?? [];
                $media->update([
                    'metadata_json' => array_merge($metadata, [
                        'ai_tags' => $tags,
                        'ai_provider' => $provider,
                        'ai_tagged_at' => now()->toIso8601String(),
                    ]),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Failed to generate AI tags for media {$media->id}: ".$e->getMessage());
        }
    }

    private function getClarifaiTags(Media $media, string $apiKey): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Key '.$apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.clarifai.com/v2/models/general-recognize/outputs', [
            'inputs' => [
                [
                    'data' => [
                        'image' => [
                            'url' => $media->url,
                        ],
                    ],
                ],
            ],
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $concepts = $data['outputs'][0]['data']['concepts'] ?? [];

            return collect($concepts)
                ->where('value', '>=', 0.7)
                ->pluck('name')
                ->map(fn ($tag) => strtolower(str_replace(' ', '-', $tag)))
                ->values()
                ->toArray();
        }

        return [];
    }

    private function getGoogleVisionTags(Media $media, string $apiKey): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
            'requests' => [
                [
                    'image' => [
                        'source' => [
                            'imageUri' => $media->url,
                        ],
                    ],
                    'features' => [
                        ['type' => 'LABEL_DETECTION', 'maxResults' => 20],
                    ],
                ],
            ],
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $labels = $data['responses'][0]['labelAnnotations'] ?? [];

            return collect($labels)
                ->where('score', '>=', 0.7)
                ->pluck('description')
                ->map(fn ($tag) => strtolower(str_replace(' ', '-', $tag)))
                ->values()
                ->toArray();
        }

        return [];
    }

    private function getAwsRekognitionTags(Media $media): array
    {
        $accessKey = (string) config('services.aws.access_key_id');
        $secretKey = (string) config('services.aws.secret_access_key');
        $region = (string) config('services.aws.region', 'us-east-1');
        if ($accessKey === '' || $secretKey === '') {
            throw new \RuntimeException('AWS Rekognition credentials are not configured.');
        }

        $bytes = Storage::disk($media->disk ?: config('filesystems.default'))->get($media->path);
        $payload = json_encode([
            'Image' => ['Bytes' => base64_encode($bytes)],
            'MaxLabels' => 20,
            'MinConfidence' => 70,
        ], JSON_THROW_ON_ERROR);
        $host = "rekognition.{$region}.amazonaws.com";
        $timestamp = now('UTC')->format('Ymd\THis\Z');
        $date = substr($timestamp, 0, 8);
        $token = (string) config('services.aws.session_token', '');
        $headers = [
            'content-type' => 'application/x-amz-json-1.1',
            'host' => $host,
            'x-amz-date' => $timestamp,
            'x-amz-target' => 'RekognitionService.DetectLabels',
        ];
        if ($token !== '') {
            $headers['x-amz-security-token'] = $token;
        }
        ksort($headers);
        $canonicalHeaders = collect($headers)->map(fn ($value, $key) => "{$key}:{$value}\n")->implode('');
        $signedHeaders = implode(';', array_keys($headers));
        $canonicalRequest = "POST\n/\n\n{$canonicalHeaders}\n{$signedHeaders}\n".hash('sha256', $payload);
        $scope = "{$date}/{$region}/rekognition/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$timestamp}\n{$scope}\n".hash('sha256', $canonicalRequest);
        $dateKey = hash_hmac('sha256', $date, 'AWS4'.$secretKey, true);
        $regionKey = hash_hmac('sha256', $region, $dateKey, true);
        $serviceKey = hash_hmac('sha256', 'rekognition', $regionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $serviceKey, true);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        $headers['authorization'] = "AWS4-HMAC-SHA256 Credential={$accessKey}/{$scope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $response = Http::withHeaders($headers)->withBody($payload, 'application/x-amz-json-1.1')
            ->post("https://{$host}/");
        $response->throw();

        return collect($response->json('Labels', []))
            ->filter(fn (array $label) => ($label['Confidence'] ?? 0) >= 70)
            ->pluck('Name')
            ->map(fn (string $tag) => strtolower(str_replace(' ', '-', $tag)))
            ->values()
            ->all();
    }
}
