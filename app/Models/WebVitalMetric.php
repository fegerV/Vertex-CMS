<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class WebVitalMetric extends Model
{
    protected $fillable = [
        'session_id',
        'url',
        'metric_type',
        'value',
        'rating',
        'metadata',
        'measured_at',
    ];

    protected $casts = [
        'value' => 'decimal:3',
        'metadata' => 'array',
        'measured_at' => 'datetime',
    ];

    /**
     * Thresholds for Web Vitals ratings
     */
    public const THRESHOLDS = [
        'LCP' => [
            'good' => 2.5,
            'needs-improvement' => 4.0,
        ],
        'FID' => [
            'good' => 0.1,
            'needs-improvement' => 0.3,
        ],
        'CLS' => [
            'good' => 0.1,
            'needs-improvement' => 0.25,
        ],
        'INP' => [
            'good' => 0.2,
            'needs-improvement' => 0.5,
        ],
        'TTFB' => [
            'good' => 0.8,
            'needs-improvement' => 1.8,
        ],
    ];

    /**
     * Get rating based on metric type and value
     */
    public static function getRating(string $metricType, float $value): string
    {
        if (!isset(self::THRESHOLDS[$metricType])) {
            return 'needs-improvement';
        }

        $thresholds = self::THRESHOLDS[$metricType];

        if ($value <= $thresholds['good']) {
            return 'good';
        } elseif ($value <= $thresholds['needs-improvement']) {
            return 'needs-improvement';
        }

        return 'poor';
    }

    /**
     * Scope to filter by metric type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('metric_type', $type);
    }

    /**
     * Scope to filter by rating
     */
    public function scopeWithRating(Builder $query, string $rating): Builder
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeForDateRange(Builder $query, \Carbon\CarbonInterface $start, \Carbon\CarbonInterface $end): Builder
    {
        return $query->whereBetween('measured_at', [$start, $end]);
    }

    /**
     * Get average value for a metric type
     */
    public static function getAverage(string $type, ?\Carbon\CarbonInterface $since = null): ?float
    {
        $query = static::query()->ofType($type);

        if ($since) {
            $query->forDateRange($since, now());
        }

        return $query->avg('value');
    }

    /**
     * Get distribution of ratings for a metric type
     */
    public static function getRatingDistribution(string $type, ?\Carbon\CarbonInterface $since = null): array
    {
        $query = static::query()->ofType($type);

        if ($since) {
            $query->forDateRange($since, now());
        }

        $distribution = $query->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        return [
            'good' => $distribution['good'] ?? 0,
            'needs-improvement' => $distribution['needs-improvement'] ?? 0,
            'poor' => $distribution['poor'] ?? 0,
        ];
    }

    /**
     * Get recent metrics grouped by URL
     */
    public static function getMetricsByUrl(int $limit = 10): \Illuminate\Support\Collection
    {
        return static::query()
            ->selectRaw('url, metric_type, AVG(value) as avg_value, COUNT(*) as count')
            ->groupBy('url', 'metric_type')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }
}
