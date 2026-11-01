<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebVitalMetric;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WebVitalsController extends Controller
{
    /**
     * Display the Web Vitals dashboard
     */
    public function dashboard(): View
    {
        $metrics = [
            'LCP' => [
                'name' => 'Largest Contentful Paint',
                'unit' => 's',
                'average' => WebVitalMetric::getAverage('LCP'),
                'distribution' => WebVitalMetric::getRatingDistribution('LCP'),
            ],
            'FID' => [
                'name' => 'First Input Delay',
                'unit' => 's',
                'average' => WebVitalMetric::getAverage('FID'),
                'distribution' => WebVitalMetric::getRatingDistribution('FID'),
            ],
            'CLS' => [
                'name' => 'Cumulative Layout Shift',
                'unit' => '',
                'average' => WebVitalMetric::getAverage('CLS'),
                'distribution' => WebVitalMetric::getRatingDistribution('CLS'),
            ],
            'INP' => [
                'name' => 'Interaction to Next Paint',
                'unit' => 's',
                'average' => WebVitalMetric::getAverage('INP'),
                'distribution' => WebVitalMetric::getRatingDistribution('INP'),
            ],
            'TTFB' => [
                'name' => 'Time to First Byte',
                'unit' => 's',
                'average' => WebVitalMetric::getAverage('TTFB'),
                'distribution' => WebVitalMetric::getRatingDistribution('TTFB'),
            ],
        ];

        $topUrls = WebVitalMetric::getMetricsByUrl(10);

        $recentMetrics = WebVitalMetric::query()
            ->withRating('poor')
            ->orderByDesc('measured_at')
            ->limit(20)
            ->get();

        $totalMeasurements = WebVitalMetric::count();
        $goodPercentage = WebVitalMetric::where('rating', 'good')->count();
        $overallScore = $totalMeasurements > 0 ? round(($goodPercentage / $totalMeasurements) * 100, 1) : 0;

        return view('admin.web-vitals.dashboard', compact(
            'metrics',
            'topUrls',
            'recentMetrics',
            'overallScore',
            'totalMeasurements'
        ));
    }

    /**
     * Get metrics data for charts (AJAX endpoint)
     */
    public function chartData(Request $request): JsonResponse
    {
        $type = $request->get('type', 'LCP');
        $days = (int) $request->get('days', 7);
        $startDate = now()->subDays($days);

        $data = WebVitalMetric::query()
            ->ofType($type)
            ->forDateRange($startDate, now())
            ->selectRaw('DATE(measured_at) as date, AVG(value) as avg_value, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'data' => $data,
            'metric_type' => $type,
            'period_days' => $days,
        ]);
    }

    /**
     * Get paginated list of all metrics
     */
    public function index(Request $request): JsonResponse
    {
        $query = WebVitalMetric::query()
            ->when($request->filled('type'), fn($q) => $q->ofType($request->get('type')))
            ->when($request->filled('rating'), fn($q) => $q->withRating($request->get('rating')))
            ->when($request->filled('url'), fn($q) => $q->where('url', 'like', '%' . $request->get('url') . '%'))
            ->orderByDesc('measured_at');

        $metrics = $query->paginate($request->get('per_page', 50));

        return response()->json($metrics);
    }

    /**
     * Export metrics to CSV
     */
    public function export(Request $request): StreamedResponse
    {
        $query = WebVitalMetric::query()
            ->when($request->filled('type'), fn ($q) => $q->ofType($request->get('type')))
            ->when($request->filled('rating'), fn ($q) => $q->withRating($request->get('rating')))
            ->orderByDesc('measured_at');

        $filename = 'web-vitals-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['ID', 'Session ID', 'URL', 'Metric Type', 'Value', 'Rating', 'Metadata', 'Measured At']);

            $query->chunk(500, function ($metrics) use ($output): void {
                foreach ($metrics as $metric) {
                    fputcsv($output, [
                        $metric->id,
                        $metric->session_id,
                        $metric->url,
                        $metric->metric_type,
                        $metric->value,
                        $metric->rating,
                        json_encode($metric->metadata),
                        $metric->measured_at?->toDateTimeString(),
                    ]);
                }
            });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
