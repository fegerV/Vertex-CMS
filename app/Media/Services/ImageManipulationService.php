<?php

namespace App\Media\Services;

use App\Models\Media;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;

/**
 * Image Optimization Service
 * 
 * Features:
 * - Image cropping
 * - WebP/AVIF conversion
 * - srcset generation for responsive images
 * - Lazy loading attributes
 */
class ImageOptimizationService
{
    /**
     * Supported output formats
     */
    protected array $supportedFormats = ['webp', 'avif', 'jpg', 'png'];

    /**
     * Default sizes for srcset generation
     */
    protected array $defaultSizes = [320, 640, 768, 1024, 1280, 1920];

    /**
     * Convert image to WebP format
     */
    public function convertToWebP(Media $media, int $quality = 85): Media
    {
        return $this->convertFormat($media, 'webp', $quality);
    }

    /**
     * Convert image to AVIF format
     */
    public function convertToAvif(Media $media, int $quality = 80): Media
    {
        return $this->convertFormat($media, 'avif', $quality);
    }

    /**
     * Convert image to specified format
     */
    public function convertFormat(Media $media, string $format, int $quality = 85): Media
    {
        $sourcePath = public_path($media->path);

        if (!File::exists($sourcePath)) {
            throw new \RuntimeException("Source file not found: {$media->path}");
        }

        if (!in_array(strtolower($format), $this->supportedFormats)) {
            throw new \InvalidArgumentException("Unsupported format: {$format}");
        }

        $image = InterventionImage::read($sourcePath);
        
        $newFilename = pathinfo($media->filename, PATHINFO_FILENAME) . '.' . $format;
        $newPath = dirname($sourcePath) . '/' . $newFilename;

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($newPath));

        // Save with specified quality
        $image->toFormat($format, ['quality' => $quality])->save($newPath);

        // Update metadata with converted versions
        $metadata = $media->metadata_json ?? [];
        $convertedVersions = $metadata['converted_versions'] ?? [];
        
        $convertedVersions[$format] = [
            'filename' => $newFilename,
            'path' => str_replace(basename($media->path), $newFilename, $media->path),
            'url' => url(str_replace(basename($media->path), $newFilename, $media->path)),
            'size' => File::size($newPath),
            'quality' => $quality,
            'created_at' => now()->toISOString(),
        ];

        $media->update([
            'metadata_json' => array_merge($metadata, ['converted_versions' => $convertedVersions]),
        ]);

        return $media;
    }

    /**
     * Generate all optimized versions (WebP + AVIF)
     */
    public function generateAllVersions(Media $media): Media
    {
        $this->convertToWebP($media);
        $this->convertToAvif($media);
        return $media;
    }

    /**
     * Crop image to specified dimensions
     */
    public function crop(Media $media, int $x, int $y, int $width, int $height): Media
    {
        $sourcePath = public_path($media->path);

        if (!File::exists($sourcePath)) {
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

    /**
     * Generate srcset for responsive images
     * 
     * @return array Array of srcset entries with width, url, and size
     */
    public function generateSrcset(Media $media, array $sizes = null, string $format = null): array
    {
        $sizes = $sizes ?? $this->defaultSizes;
        $format = $format ?? pathinfo($media->filename, PATHINFO_EXTENSION);
        
        $sourcePath = public_path($media->path);
        if (!File::exists($sourcePath)) {
            return [];
        }

        $originalWidth = $media->width;
        $srcsetEntries = [];

        foreach ($sizes as $size) {
            // Skip sizes larger than original
            if ($size > $originalWidth) {
                continue;
            }

            $thumbnailFolder = dirname($sourcePath) . '/thumbnails';
            File::ensureDirectoryExists($thumbnailFolder);

            $filename = pathinfo($media->filename, PATHINFO_FILENAME);
            $extension = $format;
            $thumbnailFilename = "{$filename}_{$size}w.{$extension}";
            $thumbnailPath = $thumbnailFolder . '/' . $thumbnailFilename;

            // Generate resized version if it doesn't exist
            if (!File::exists($thumbnailPath)) {
                $image = InterventionImage::read($sourcePath);
                $image->resize($size, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->toFormat($extension)->save($thumbnailPath);
            }

            $thumbnailUrl = str_replace(public_path(''), '', $thumbnailPath);
            $thumbnailUrl = str_replace('\\', '/', $thumbnailUrl);

            $srcsetEntries[] = [
                'width' => $size,
                'url' => url($thumbnailUrl),
                'descriptor' => "{$size}w",
                'size' => File::size($thumbnailPath),
            ];
        }

        // Update metadata with srcset info
        $metadata = $media->metadata_json ?? [];
        $metadata['srcset'] = $srcsetEntries;
        $metadata['srcset_generated_at'] = now()->toISOString();
        
        $media->update(['metadata_json' => $metadata]);

        return $srcsetEntries;
    }

    /**
     * Get srcset as HTML attribute string
     */
    public function getSrcsetAttribute(Media $media, array $sizes = null, string $format = null): string
    {
        $entries = $this->generateSrcset($media, $sizes, $format);
        
        if (empty($entries)) {
            return '';
        }

        return collect($entries)
            ->map(fn($entry) => "{$entry['url']} {$entry['descriptor']}")
            ->join(', ');
    }

    /**
     * Get sizes attribute based on breakpoints
     */
    public function getSizesAttribute(array $breakpoints = null): string
    {
        $breakpoints = $breakpoints ?? [
            '(max-width: 640px) 320px',
            '(max-width: 768px) 640px',
            '(max-width: 1024px) 768px',
            '(max-width: 1280px) 1024px',
            '1280px',
        ];

        return implode(', ', $breakpoints);
    }

    /**
     * Generate complete responsive image HTML with srcset, sizes, and lazy loading
     */
    public function renderResponsiveImage(
        Media $media,
        array $options = []
    ): string {
        $alt = $options['alt'] ?? $media->alt_text ?? $media->filename;
        $class = $options['class'] ?? '';
        $loading = $options['loading'] ?? 'lazy';
        $decoding = $options['decoding'] ?? 'async';
        $width = $options['width'] ?? $media->width;
        $height = $options['height'] ?? $media->height;
        $format = $options['format'] ?? null;
        $sizes = $options['sizes'] ?? null;
        $srcsetSizes = $options['srcset_sizes'] ?? null;

        // Get source URL (prefer WebP/AVIF if available)
        $metadata = $media->metadata_json ?? [];
        $srcUrl = $media->url;
        
        if ($format && isset($metadata['converted_versions'][$format])) {
            $srcUrl = $metadata['converted_versions'][$format]['url'];
        } elseif (isset($metadata['converted_versions']['webp'])) {
            $srcUrl = $metadata['converted_versions']['webp']['url'];
        }

        // Build srcset attribute
        $srcset = $this->getSrcsetAttribute($media, $srcsetSizes, $format);
        
        // Build sizes attribute
        $sizesAttr = $this->getSizesAttribute($sizes);

        // Build picture element with multiple sources
        $html = '<picture>';
        
        // Add AVIF source if available
        if (isset($metadata['converted_versions']['avif'])) {
            $avifSrcset = $this->getSrcsetAttribute($media, $srcsetSizes, 'avif');
            if ($avifSrcset) {
                $html .= sprintf(
                    '<source srcset="%s" sizes="%s" type="image/avif">',
                    htmlspecialchars($avifSrcset, ENT_QUOTES),
                    $sizesAttr
                );
            }
        }
        
        // Add WebP source if available
        if (isset($metadata['converted_versions']['webp'])) {
            $webpSrcset = $this->getSrcsetAttribute($media, $srcsetSizes, 'webp');
            if ($webpSrcset) {
                $html .= sprintf(
                    '<source srcset="%s" sizes="%s" type="image/webp">',
                    htmlspecialchars($webpSrcset, ENT_QUOTES),
                    $sizesAttr
                );
            }
        }

        // Add fallback img tag
        $imgAttrs = [
            'src' => htmlspecialchars($srcUrl, ENT_QUOTES),
            'alt' => htmlspecialchars($alt, ENT_QUOTES),
            'loading' => $loading,
            'decoding' => $decoding,
        ];

        if ($srcset) {
            $imgAttrs['srcset'] = htmlspecialchars($srcset, ENT_QUOTES);
        }
        $imgAttrs['sizes'] = $sizesAttr;

        if ($width) {
            $imgAttrs['width'] = $width;
        }
        if ($height) {
            $imgAttrs['height'] = $height;
        }
        if ($class) {
            $imgAttrs['class'] = htmlspecialchars($class, ENT_QUOTES);
        }

        $imgTag = '<img';
        foreach ($imgAttrs as $key => $value) {
            $imgTag .= " {$key}=\"{$value}\"";
        }
        $imgTag .= '>';

        $html .= $imgTag;
        $html .= '</picture>';

        return $html;
    }

    /**
     * Get lazy loading HTML attributes
     */
    public function getLazyLoadingAttributes(array $options = []): string
    {
        $loading = $options['loading'] ?? 'lazy';
        $decoding = $options['decoding'] ?? 'async';
        $fetchPriority = $options['fetchpriority'] ?? 'auto';

        return sprintf(
            'loading="%s" decoding="%s" fetchpriority="%s"',
            $loading,
            $decoding,
            $fetchPriority
        );
    }

    /**
     * Optimize image with all features
     */
    public function optimize(Media $media, array $options = []): Media
    {
        $generateWebP = $options['webp'] ?? true;
        $generateAvif = $options['avif'] ?? true;
        $generateSrcset = $options['srcset'] ?? true;
        $srcsetSizes = $options['srcset_sizes'] ?? $this->defaultSizes;

        if ($generateWebP) {
            $this->convertToWebP($media);
        }

        if ($generateAvif) {
            $this->convertToAvif($media);
        }

        if ($generateSrcset) {
            $this->generateSrcset($media, $srcsetSizes);
        }

        return $media;
    }

    /**
     * Get dimensions from image file
     */
    private function getDimensions(string $path): array
    {
        $size = @getimagesize($path);
        return $size ? [$size[0], $size[1]] : [null, null];
    }
}
