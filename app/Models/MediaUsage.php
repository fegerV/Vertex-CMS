<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaUsage extends Model
{
    protected $table = 'media_usages';

    protected $fillable = [
        'media_id',
        'usable_type',
        'usable_id',
        'field_name',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function usable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getUsableNameAttribute(): string
    {
        $modelClass = $this->usable_type;
        
        // Простое имя модели (например, "Page" из "App\Models\Page")
        $modelName = class_basename($modelClass);
        
        // Перевод имен моделей в понятные названия
        $translations = [
            'Page' => 'Страница',
            'Product' => 'Товар',
            'Post' => 'Запись',
            'User' => 'Пользователь',
            'Category' => 'Категория',
        ];
        
        return $translations[$modelName] ?? $modelName;
    }

    public function getUsableUrlAttribute(): ?string
    {
        if (!$this->usable) {
            return null;
        }

        // Генерация URL в зависимости от типа модели
        return match (get_class($this->usable)) {
            'App\Models\Page' => route('admin.pages.edit', $this->usable->id),
            'App\Ecommerce\Models\Product' => route('admin.ecommerce.products.edit', $this->usable->id),
            default => null,
        };
    }
}
