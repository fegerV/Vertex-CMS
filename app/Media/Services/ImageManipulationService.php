<?php

namespace App\Media\Services;

use App\Models\Media;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;

class ImageManipulationService
{
    public function resize(Media $media, int $width, ?int $height = null, string $mode = 'contain'): Media
    {
        $sourcePath = public_path($media->path);
        
        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("Source file not found: {$media->path}");
        }

        $thumbnailFolder = dirname($sourcePath) . '/thumbnails';
        File::ensureDirectoryExists($thumbnailFolder);

        $filename = pathinfo($media->filename, PATHINFO_FILENAME);
        $extension = $media->extension;
        $thumbnailFilename = "{$filename}_{$width}x{$height}.{$extension}";
        $thumbnailPath = $thumbnailFolder . '/' . $thumbnailFilename;

        $image = InterventionImage::read($sourcePath);

        if ($mode === 'cover') {
            $image->cover($width, $height ?? $width);
        } else {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $image->save($thumbnailPath);

        $thumbnailUrl = str_replace(public_path(''), '', $thumbnailPath);
        $thumbnailUrl = str_replace('\\', '/', $thumbnailUrl);

        $metadata = $media->metadata_json ?? [];
        $thumbnails = $metadata['thumbnails'] ?? [];
        
        $thumbnails[] = [
            'width' => $width,
            'height' => $height,
            'mode' => $mode,
            'filename' => $thumbnailFilename,
            'path' => ltrim($thumbnailUrl, '/'),
            'url' => url($thumbnailUrl),
            'size' => File::size($thumbnailPath),
        ];

        $media->update([
            'metadata_json' => array_merge($metadata, ['thumbnails' => $thumbnails]),
        ]);

        return $media;
    }

    public function crop(Media $media, int $x, int $y, int $width, int $height): Media
    {
        $sourcePath = public_path($media->path);

        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("Source file not found: {$media->path}");
        }

        $image = InterventionImage::read($sourcePath);
        $image->crop($width, $height, $x, $y);
        $image->save($sourcePath);

        [$newWidth, $newHeight] = $this->getDimensions($sourcePath);

        $media->update([
            'width' => $newWidth,
            'height' => $newHeight,
        ]);

        return $media;
    }

    public function watermark(Media $media, string $text, array $options = []): Media
    {
        $sourcePath = public_path($media->path);

        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("Source file not found: {$media->path}");
        }

        $image = InterventionImage::read($sourcePath);

        $font = $options['font'] ?? resource_path('fonts/arial.ttf');
        $size = $options['size'] ?? 24;
        $color = $options['color'] ?? '#ffffff';
        $position = $options['position'] ?? 'bottom-right';
        $offsetX = $options['offset_x'] ?? 20;
        $offsetY = $options['offset_y'] ?? 20;

        $image->text($text, $offsetX, $offsetY, function ($fontConfig) use ($font, $size, $color, $position) {
            $fontConfig->file($font);
            $fontConfig->size($size);
            $fontConfig->color($color);
            
            match ($position) {
                'top-left' => fn () => $fontConfig->align('left')->valign('top'),
                'top-right' => fn () => $fontConfig->align('right')->valign('top'),
                'bottom-left' => fn () => $fontConfig->align('left')->valign('bottom'),
                'bottom-right' => fn () => $fontConfig->align('right')->valign('bottom'),
                'center' => fn () => $fontConfig->align('center')->valign('middle'),
                default => fn () => $fontConfig->align('right')->valign('bottom'),
            };
        });

        $image->save($sourcePath);

        return $media;
    }

    public function convertFormat(Media $media, string $format): Media
    {
        $sourcePath = public_path($media->path);

        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("Source file not found: {$media->path}");
        }

        $image = InterventionImage::read($sourcePath);
        
        $newFilename = pathinfo($media->filename, PATHINFO_FILENAME) . '.' . $format;
        $newPath = dirname($sourcePath) . '/' . $newFilename;

        $image->toFormat($format)->save($newPath);

        $media->update([
            'filename' => $newFilename,
            'extension' => $format,
            'mime_type' => mime_content_type($newPath),
            'path' => str_replace(basename($media->path), $newFilename, $media->path),
            'url' => url(str_replace(basename($media->path), $newFilename, $media->path)),
        ]);

        if ($sourcePath !== $newPath) {
            File::delete($sourcePath);
        }

        return $media;
    }

    private function getDimensions(string $path): array
    {
        $size = @getimagesize($path);
        return $size ? [$size[0], $size[1]] : [null, null];
    }
}
