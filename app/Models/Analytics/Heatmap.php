<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class Heatmap extends Model
{
    protected $fillable = [
        'page_url',
        'heatmap_type', // click, move, scroll
        'data_points',
        'viewport_width',
        'viewport_height',
        'session_count',
        'date_range_start',
        'date_range_end',
    ];

    protected $casts = [
        'data_points' => 'array',
        'viewport_width' => 'integer',
        'viewport_height' => 'integer',
        'session_count' => 'integer',
        'date_range_start' => 'datetime',
        'date_range_end' => 'datetime',
    ];

    public function getAggregatedDataAttribute()
    {
        return collect($this->data_points)
            ->groupBy(['x', 'y'])
            ->map(fn($points) => $points->count())
            ->toArray();
    }
}
