<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'title',
        'description',
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
}

