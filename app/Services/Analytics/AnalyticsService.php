<?php

namespace App\Services\Analytics;

use App\Models\Analytics\Dashboard;
use App\Models\Analytics\FunnelStep;
use App\Models\Analytics\Heatmap;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getDashboardData(Dashboard $dashboard, array $filters = [])
    {
        $widgets = $dashboard->widgets ?? [];
        $data = [];

        foreach ($widgets as $widget) {
            $data[$widget['id']] = $this->getWidgetData($widget, $filters);
        }

        return $data;
    }

    public function getWidgetData(array $widget, array $filters = [])
    {
        return match ($widget['type']) {
            'funnel' => $this->getFunnelData($widget['config'] ?? [], $filters),
            'heatmap' => $this->getHeatmapData($widget['config'] ?? [], $filters),
            'chart' => $this->getChartData($widget['config'] ?? [], $filters),
            default => [],
        };
    }

    public function getFunnelData(array $config, array $filters = [])
    {
        $steps = FunnelStep::where('dashboard_id', $config['dashboard_id'] ?? null)
            ->orderBy('step_order')
            ->get();

        $totalUsers = 0;
        $stepData = [];

        foreach ($steps as $step) {
            $count = $this->calculateEventCount($step->event_name, $filters);

            if ($totalUsers === 0) {
                $totalUsers = $count;
            }

            $conversionRate = $totalUsers > 0 ? ($count / $totalUsers) * 100 : 0;
            $dropOffRate = 100 - $conversionRate;

            $stepData[] = [
                'name' => $step->name,
                'count' => $count,
                'conversion_rate' => round($conversionRate, 2),
                'drop_off_rate' => round($dropOffRate, 2),
            ];
        }

        return $stepData;
    }

    public function getHeatmapData(array $config, array $filters = [])
    {
        $query = Heatmap::query();

        if (isset($config['page_url'])) {
            $query->where('page_url', $config['page_url']);
        }

        if (isset($config['heatmap_type'])) {
            $query->where('heatmap_type', $config['heatmap_type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('date_range_start', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('date_range_end', '<=', $filters['date_to']);
        }

        $heatmaps = $query->get();

        return [
            'aggregated_points' => $heatmaps->flatMap->data_points,
            'session_count' => $heatmaps->sum('session_count'),
            'viewport' => [
                'width' => $heatmaps->first()?->viewport_width ?? 1920,
                'height' => $heatmaps->first()?->viewport_height ?? 1080,
            ],
        ];
    }

    public function getChartData(array $config, array $filters = [])
    {
        $metric = in_array($config['metric'] ?? 'visits', ['visits', 'visitors'], true)
            ? ($config['metric'] ?? 'visits')
            : 'visits';
        $query = DB::table('analytics_aggregates')
            ->select('visit_date', DB::raw("SUM({$metric}) AS aggregate_value"))
            ->when($config['kind'] ?? null, fn ($builder, $kind) => $builder->where('kind', $kind))
            ->when($filters['date_from'] ?? null, fn ($builder, $date) => $builder->whereDate('visit_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($builder, $date) => $builder->whereDate('visit_date', '<=', $date))
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->get();

        return [
            'labels' => $query->pluck('visit_date')->all(),
            'datasets' => [[
                'label' => ucfirst($metric),
                'data' => $query->pluck('aggregate_value')->map(fn ($value) => (int) $value)->all(),
            ]],
        ];
    }

    private function calculateEventCount(string $eventName, array $filters = []): int
    {
        return (int) DB::table('analytics_aggregates')
            ->where('kind', $eventName)
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('visit_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('visit_date', '<=', $date))
            ->sum('visits');
    }

    public function recordHeatmapData(string $url, string $type, array $points, int $viewportWidth, int $viewportHeight)
    {
        Heatmap::updateOrCreate(
            [
                'page_url' => $url,
                'heatmap_type' => $type,
                'date_range_start' => Carbon::today(),
                'date_range_end' => Carbon::today(),
            ],
            [
                'data_points' => array_merge(
                    Heatmap::where('page_url', $url)
                        ->where('heatmap_type', $type)
                        ->whereDate('date_range_start', Carbon::today())
                        ->value('data_points') ?? [],
                    $points
                ),
                'viewport_width' => $viewportWidth,
                'viewport_height' => $viewportHeight,
                'session_count' => DB::raw('session_count + 1'),
            ]
        );
    }
}
