<?php

namespace App\Media\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class TranscodeVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $mediaId,
        public array $formats = ['mp4', 'webm'],
        public ?string $quality = 'medium',
    ) {
    }

    public function handle(): void
    {
        $media = Media::findOrFail($this->mediaId);

        if (! str_starts_with($media->mime_type, 'video/')) {
            throw new \RuntimeException("Media {$media->id} is not a video file.");
        }

        $sourcePath = public_path($media->path);

        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("Source video file not found: {$media->path}");
        }

        $qualitySettings = [
            'low' => ['crf' => 28, 'bitrate' => '500k'],
            'medium' => ['crf' => 23, 'bitrate' => '1500k'],
            'high' => ['crf' => 18, 'bitrate' => '4000k'],
        ];

        $settings = $qualitySettings[$this->quality] ?? $qualitySettings['medium'];

        foreach ($this->formats as $format) {
            try {
                $outputFilename = pathinfo($media->filename, PATHINFO_FILENAME) . "_{$format}." . $format;
                $outputPath = dirname($sourcePath) . '/' . $outputFilename;

                $this->transcodeWithFfmpeg($sourcePath, $outputPath, $format, $settings);

                if (File::exists($outputPath)) {
                    $relativePath = str_replace(public_path(''), '', $outputPath);
                    $relativePath = str_replace('\\', '/', $relativePath);

                    $metadata = $media->metadata_json ?? [];
                    $transcoded = $metadata['transcoded'] ?? [];

                    $transcoded[] = [
                        'format' => $format,
                        'quality' => $this->quality,
                        'filename' => $outputFilename,
                        'path' => ltrim($relativePath, '/'),
                        'url' => url($relativePath),
                        'size' => File::size($outputPath),
                        'created_at' => now()->toIso8601String(),
                    ];

                    $media->update([
                        'metadata_json' => array_merge($metadata, ['transcoded' => $transcoded]),
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error("Failed to transcode video {$media->id} to {$format}: " . $e->getMessage());
            }
        }
    }

    private function transcodeWithFfmpeg(string $sourcePath, string $outputPath, string $format, array $settings): void
    {
        if (! $this->ffmpegExists()) {
            throw new \RuntimeException('FFmpeg is not installed on this system.');
        }

        $command = match ($format) {
            'mp4' => sprintf(
                'ffmpeg -i %s -c:v libx264 -crf %d -b:v %s -c:a aac -b:a 128k -y %s 2>&1',
                escapeshellarg($sourcePath),
                $settings['crf'],
                $settings['bitrate'],
                escapeshellarg($outputPath)
            ),
            'webm' => sprintf(
                'ffmpeg -i %s -c:v libvpx-vp9 -crf %d -b:v %s -c:a libopus -b:a 128k -y %s 2>&1',
                escapeshellarg($sourcePath),
                $settings['crf'],
                $settings['bitrate'],
                escapeshellarg($outputPath)
            ),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \RuntimeException("FFmpeg transcoding failed: " . implode("\n", $output));
        }
    }

    private function ffmpegExists(): bool
    {
        exec('which ffmpeg', $output, $returnCode);
        return $returnCode === 0;
    }
}
