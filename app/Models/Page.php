<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'uri',
        'status',
        'template',
        'locale',
        'translation_group',
        'content_json',
        'custom_fields_json',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'content_json' => 'array',
        'custom_fields_json' => 'array',
        'published_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class);
    }

    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'entity');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Page::class, 'translation_group', 'translation_group')
            ->where('id', '!=', $this->id);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && ($this->published_at === null || $this->published_at->isPast());
    }
}
