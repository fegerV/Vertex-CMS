<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'title',
        'description',
        'keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'schema_json',
        'include_in_sitemap',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'include_in_sitemap' => 'boolean',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image');
    }
}
