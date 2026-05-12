<?php

namespace App\Media\Services;

use App\Models\Media;
use App\Models\User;
use App\System\Services\ActivityLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'alt' => $metadata['alt'] ?? null,
            'title' => $metadata['title'] ?? null,
            'caption' => $metadata['caption'] ?? null,
            'metadata_json' => [
                'thumbnails' => [],
            ],
            'created_by' => $user->id,
        ]);

        $this->activityLog->record('media.upload', 'media', $media->id, "Media \"{$media->original_filename}\" uploaded.");

        return $media;
    }

    public function update(Media $media, array $payload): Media
    {
        $allowed = ['alt', 'title', 'caption', 'folder_id'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $payload)) {
                $media->$field = $payload[$field];
            }
        }

        $media->save();

        $this->activityLog->record('media.edit', 'media', $media->id, "Media \"{$media->original_filename}\" updated.");

        return $media;
    }

    public function delete(Media $media): void
    {
        $path = public_path($media->path);

        if (File::exists($path)) {
            File::delete($path);
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
}


