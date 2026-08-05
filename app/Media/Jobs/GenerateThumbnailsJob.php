<?php

namespace App\Media\Jobs;

use App\Models\Media;
use App\Media\Services\ImageManipulationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateThumbnailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $mediaId,
        public array $sizes = [],
    ) {
    }

    public function handle(ImageManipulationService $imageService): void
    {
        $media = Media::findOrFail($this->mediaId);

        $defaultSizes = [
            ['width' => 320, 'height' => null, 'mode' => 'contain'],
            ['width' => 640, 'height' => null, 'mode' => 'contain'],
            ['width' => 1024, 'height' => null, 'mode' => 'contain'],
            ['width' => 400, 'height' => 400, 'mode' => 'cover'],
        ];

        $sizes = ! empty($this->sizes) ? $this->sizes : $defaultSizes;

        foreach ($sizes as $size) {
            try {
                $imageService->resize(
                    $media,
                    $size['width'],
                    $size['height'] ?? null,
                    $size['mode'] ?? 'contain'
                );
            } catch (\Exception $e) {
                \Log::error("Failed to generate thumbnail for media {$media->id}: " . $e->getMessage());
            }
        }
    }
}
