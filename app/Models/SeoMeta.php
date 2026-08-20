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
        'sitemap_priority',
        'sitemap_changefreq',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'include_in_sitemap' => 'boolean',
        'sitemap_priority' => 'decimal:1',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image');
    }

    /**
     * Get the sitemap priority value (0.0 to 1.0)
     */
    public function getPriorityAttribute(): float
    {
        return min(1.0, max(0.0, $this->sitemap_priority ?? 0.5));
    }

    /**
     * Get the sitemap change frequency
     */
    public function getChangefreqAttribute(): string
    {
        $validFreqs = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
        $freq = $this->sitemap_changefreq ?? 'weekly';
        
        return in_array($freq, $validFreqs) ? $freq : 'weekly';
    }
}
