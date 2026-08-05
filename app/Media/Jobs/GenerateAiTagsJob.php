<?php

namespace App\Media\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GenerateAiTagsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $mediaId,
        public ?string $provider = null,
    ) {
    }

    public function handle(): void
    {
        $media = Media::findOrFail($this->mediaId);

        if (! str_starts_with($media->mime_type, 'image/')) {
            throw new \RuntimeException("Media {$media->id} is not an image file.");
        }

        $provider = $this->provider ?? config('media.ai_tagging.provider', 'clarifai');
        $apiKey = config("services.{$provider}.api_key");

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
            \Log::error("Failed to generate AI tags for media {$media->id}: " . $e->getMessage());
        }
    }

    private function getClarifaiTags(Media $media, string $apiKey): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Key ' . $apiKey,
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
        // AWS Rekognition requires AWS SDK for PHP
        // This is a placeholder for the implementation
        \Log::info('AWS Rekognition tagging not implemented yet.');

        return [];
    }
}
