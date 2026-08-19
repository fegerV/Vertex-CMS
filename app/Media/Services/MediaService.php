<?php

namespace App\Media\Services;

use App\Models\Media;
use App\Models\MediaTag;
use App\Models\MediaVersion;
use App\Models\User;
use App\System\Services\ActivityLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function upload(UploadedFile $file, User $user, array $metadata = []): Media
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $originalFilename = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream';

        if (! in_array($extension, config('vertex.uploads.allowed_extensions'), true)) {
            throw ValidationException::withMessages([
                'file' => 'Недопустимый тип файла.',
            ]);
        }

        $folder = 'uploads/'.now()->format('Y/m');
        $directory = public_path($folder);
        File::ensureDirectoryExists($directory);

        $filename = Str::uuid().'.'.$extension;
        $target = $directory.DIRECTORY_SEPARATOR.$filename;

        if ($extension === 'svg') {
            File::put($target, $this->sanitizeSvg((string) File::get($file->getRealPath())));
        } else {
            $file->move($directory, $filename);
        }

        [$width, $height] = $this->dimensions($target, $extension);
        
        // Извлекаем EXIF данные для изображений
        $exifData = $this->extractExif($target, $extension);
        
        // Автоматическая генерация alt и title если не указаны
        $alt = $metadata['alt'] ?? null;
        $title = $metadata['title'] ?? null;
        
        if ($this->isImageType($extension) && empty($alt)) {
            $aiData = $this->generateAiData($originalFilename, $exifData);
            $alt = $aiData['alt'] ?? null;
            $title = $title ?? $aiData['title'] ?? null;
        }

        $media = Media::query()->create([
            'disk' => 'public',
            'folder_id' => $metadata['folder_id'] ?? null,
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => File::size($target),
            'width' => $width,
            'height' => $height,
            'path' => "{$folder}/{$filename}",
            'url' => url("{$folder}/{$filename}"),
            'alt' => $alt,
            'title' => $title,
            'caption' => $metadata['caption'] ?? null,
            'metadata_json' => [
                'thumbnails' => [],
            ],
            'tags_json' => $metadata['tags'] ?? [],
            'is_optimized' => false,
            'exif_data_json' => $exifData,
            'ai_data_json' => isset($aiData) ? $aiData : null,
            'created_by' => $user->id,
        ]);

        // Создаем теги если переданы
        if (!empty($metadata['tags'])) {
            $media->syncTags($metadata['tags']);
        }

        // Создаем начальную версию файла
        $media->createVersion([
            'disk' => 'public',
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'size' => File::size($target),
            'width' => $width,
            'height' => $height,
            'path' => "{$folder}/{$filename}",
            'url' => url("{$folder}/{$filename}"),
            'changes_description' => 'Начальная загрузка файла',
        ], $user);

        $this->activityLog->record('media.upload', 'media', $media->id, "Media \"{$media->original_filename}\" uploaded.");

        return $media;
    }

    public function update(Media $media, array $payload): Media
    {
        $allowed = ['alt', 'title', 'caption', 'folder_id', 'tags_json', 'is_optimized', 'exif_data_json', 'ai_data_json'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $payload)) {
                $media->$field = $payload[$field];
            }
        }

        $media->save();

        // Синхронизируем теги если переданы
        if (isset($payload['tags'])) {
            $media->syncTags($payload['tags']);
        }

        $this->activityLog->record('media.edit', 'media', $media->id, "Media \"{$media->original_filename}\" updated.");

        return $media;
    }

    public function delete(Media $media): void
    {
        // Проверяем где используется файл
        $usageCount = $media->usages()->count();
        if ($usageCount > 0) {
            throw ValidationException::withMessages([
                'file' => "Нельзя удалить файл. Он используется в {$usageCount} местах.",
            ]);
        }

        $path = public_path($media->path);

        if (File::exists($path)) {
            File::delete($path);
        }

        // Удаляем все версии файла
        foreach ($media->versions as $version) {
            $versionPath = public_path($version->path);
            if (File::exists($versionPath)) {
                File::delete($versionPath);
            }
        }

        $this->activityLog->record('media.delete', 'media', $media->id, "Media \"{$media->original_filename}\" deleted.");

        $media->delete();
    }

    public function move(Media $media, ?int $folderId): Media
    {
        $media->folder_id = $folderId;
        $media->save();

        $this->activityLog->record('media.move', 'media', $media->id, "Media \"{$media->original_filename}\" moved to folder {$folderId}.");

        return $media;
    }

    public function bulkDelete(array $mediaIds, User $user): array
    {
        $deleted = [];
        $failed = [];

        foreach ($mediaIds as $id) {
            try {
                $media = Media::findOrFail($id);
                $this->delete($media);
                $deleted[] = $id;
            } catch (\Exception $e) {
                $failed[] = [
                    'id' => $id,
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return [
            'deleted' => $deleted,
            'failed' => $failed,
        ];
    }

    public function bulkMove(array $mediaIds, ?int $folderId, User $user): int
    {
        $count = 0;
        foreach ($mediaIds as $id) {
            $media = Media::find($id);
            if ($media) {
                $this->move($media, $folderId);
                $count++;
            }
        }
        return $count;
    }

    public function createVersion(Media $media, string $newFilePath, string $description, User $user): MediaVersion
    {
        $extension = pathinfo($newFilePath, PATHINFO_EXTENSION);
        [$width, $height] = $this->dimensions($newFilePath, $extension);
        
        $folder = 'uploads/versions/'.now()->format('Y/m');
        $directory = public_path($folder);
        File::ensureDirectoryExists($directory);
        
        $filename = Str::uuid().'_v'.($media->versions->count() + 1).'.'.$extension;
        $target = $directory.DIRECTORY_SEPARATOR.$filename;
        
        File::copy($newFilePath, $target);
        
        return $media->createVersion([
            'disk' => 'public',
            'filename' => $filename,
            'original_filename' => $media->original_filename,
            'size' => File::size($target),
            'width' => $width,
            'height' => $height,
            'path' => "{$folder}/{$filename}",
            'url' => url("{$folder}/{$filename}"),
            'changes_description' => $description,
        ], $user);
    }

    public function revertToVersion(MediaVersion $version): Media
    {
        $media = $version->media;
        
        // Копируем файл версии в основное хранилище
        $sourcePath = public_path($version->path);
        $targetFolder = 'uploads/'.now()->format('Y/m');
        $targetDirectory = public_path($targetFolder);
        File::ensureDirectoryExists($targetDirectory);
        
        $extension = pathinfo($version->filename, PATHINFO_EXTENSION);
        $newFilename = Str::uuid().'_reverted.'.$extension;
        $targetPath = $targetDirectory.DIRECTORY_SEPARATOR.$newFilename;
        
        File::copy($sourcePath, $targetPath);
        
        // Обновляем основной файл
        $media->update([
            'filename' => $newFilename,
            'path' => "{$targetFolder}/{$newFilename}",
            'url' => url("{$targetFolder}/{$newFilename}"),
            'size' => $version->size,
            'width' => $version->width,
            'height' => $version->height,
        ]);
        
        $this->activityLog->record('media.revert', 'media', $media->id, "Media reverted to version {$version->id}.");
        
        return $media;
    }

    public function getUsageStats(Media $media): array
    {
        $usages = $media->usages()->get();
        
        $stats = [
            'total' => $usages->count(),
            'by_type' => [],
            'items' => [],
        ];
        
        foreach ($usages as $usage) {
            $typeName = $usage->usable_name;
            
            if (!isset($stats['by_type'][$typeName])) {
                $stats['by_type'][$typeName] = 0;
            }
            $stats['by_type'][$typeName]++;
            
            $stats['items'][] = [
                'type' => $typeName,
                'id' => $usage->usable_id,
                'field' => $usage->field_name,
                'url' => $usage->usable_url,
                'model' => $usage->usable,
            ];
        }
        
        return $stats;
    }

    public function optimizeImage(Media $media): bool
    {
        if (!$media->isImage()) {
            return false;
        }

        try {
            $image = Image::read(public_path($media->path));
            $image->optimize();
            $image->save();
            
            $media->update(['is_optimized' => true]);
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function sanitizeSvg(string $svg): string
    {
        $svg = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $svg) ?? $svg;
        $svg = preg_replace('/\son\w+=(["\']).*?\1/i', '', $svg) ?? $svg;

        return str_ireplace(['javascript:', 'data:text/html'], '', $svg);
    }

    private function dimensions(string $path, string $extension): array
    {
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return [null, null];
        }

        $size = @getimagesize($path);

        return $size ? [$size[0], $size[1]] : [null, null];
    }

    private function isImageType(string $extension): bool
    {
        return in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'tiff'], true);
    }

    private function extractExif(string $path, string $extension): ?array
    {
        if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'tiff'], true)) {
            return null;
        }

        if (!function_exists('exif_read_data')) {
            return null;
        }

        try {
            $exif = @exif_read_data($path);
            if ($exif === false) {
                return null;
            }

            return [
                'make' => $exif['Make'] ?? null,
                'model' => $exif['Model'] ?? null,
                'datetime' => $exif['DateTimeOriginal'] ?? ($exif['DateTime'] ?? null),
                'exposure' => $exif['ExposureTime'] ?? null,
                'aperture' => $exif['FNumber'] ?? null,
                'iso' => $exif['ISOSpeedRatings'] ?? null,
                'focal_length' => $exif['FocalLength'] ?? null,
                'gps' => isset($exif['GPSLatitude']) && isset($exif['GPSLongitude']) ? [
                    'latitude' => $exif['GPSLatitude'],
                    'longitude' => $exif['GPSLongitude'],
                ] : null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function generateAiData(string $filename, ?array $exifData): array
    {
        // Симуляция AI-анализа (в реальности здесь был бы вызов API)
        $nameParts = pathinfo($filename, PATHINFO_FILENAME);
        $cleanName = ucwords(str_replace(['-', '_'], ' ', $nameParts));
        
        $data = [
            'alt' => $cleanName,
            'title' => $cleanName,
            'keywords' => [$cleanName],
            'analyzed_at' => now()->toIso8601String(),
        ];

        if ($exifData) {
            if (isset($exifData['model'])) {
                $data['keywords'][] = $exifData['model'];
            }
            if (isset($exifData['datetime'])) {
                $data['taken_at'] = $exifData['datetime'];
            }
        }

        return $data;
    }
}


