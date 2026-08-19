<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'disk',
        'folder_id',
        'filename',
        'original_filename',
        'mime_type',
        'extension',
        'size',
        'width',
        'height',
        'path',
        'url',
        'alt',
        'title',
        'caption',
        'metadata_json',
        'tags_json',
        'is_optimized',
        'exif_data_json',
        'ai_data_json',
        'created_by',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'tags_json' => 'array',
        'exif_data_json' => 'array',
        'ai_data_json' => 'array',
        'is_optimized' => 'boolean',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(MediaTag::class, 'media_taggable', 'media_id', 'media_tag_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MediaVersion::class, 'media_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class, 'media_id');
    }

    public function latestVersion(): ?MediaVersion
    {
        return $this->versions()->latest()->first();
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isSvg(): bool
    {
        return $this->mime_type === 'image/svg+xml';
    }

    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function addTag(string $tagName): void
    {
        $tag = MediaTag::firstOrCreate(
            ['name' => $tagName],
            ['slug' => \Str::slug($tagName)]
        );
        
        if (!$this->tags->contains($tag)) {
            $this->tags()->attach($tag);
        }
    }

    public function removeTag(string $tagName): void
    {
        $tag = MediaTag::where('name', $tagName)->first();
        if ($tag) {
            $this->tags()->detach($tag);
        }
    }

    public function syncTags(array $tagNames): void
    {
        $tags = [];
        foreach ($tagNames as $tagName) {
            $tag = MediaTag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => \Str::slug($tagName)]
            );
            $tags[] = $tag->id;
        }
        $this->tags()->sync($tags);
    }

    public function trackUsage(Model $model, string $fieldName = 'content'): void
    {
        MediaUsage::firstOrCreate([
            'media_id' => $this->id,
            'usable_type' => get_class($model),
            'usable_id' => $model->id,
            'field_name' => $fieldName,
        ]);
    }

    public function removeUsage(Model $model, string $fieldName = 'content'): void
    {
        MediaUsage::where([
            'media_id' => $this->id,
            'usable_type' => get_class($model),
            'usable_id' => $model->id,
            'field_name' => $fieldName,
        ])->delete();
    }

    public function createVersion(array $data, ?User $user = null): MediaVersion
    {
        return $this->versions()->create([
            ...$data,
            'created_by' => $user?->id,
        ]);
    }
}

