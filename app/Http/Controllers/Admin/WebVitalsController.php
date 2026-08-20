<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebVitalMetric;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

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
     * Store a new Web Vital metric (API endpoint for tracking script)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metric_type' => 'required|string|in:LCP,FID,CLS,INP,TTFB',
            'value' => 'required|numeric|min:0',
            'url' => 'nullable|url',
            'session_id' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
            'measured_at' => 'nullable|date',
        ]);

        $rating = WebVitalMetric::getRating($validated['metric_type'], (float) $validated['value']);

        $metric = WebVitalMetric::create([
            'session_id' => $validated['session_id'] ?? null,
            'url' => $validated['url'] ?? null,
            'metric_type' => $validated['metric_type'],
            'value' => $validated['value'],
            'rating' => $rating,
            'metadata' => $validated['metadata'] ?? null,
            'measured_at' => $validated['measured_at'] ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'metric_id' => $metric->id,
            'rating' => $rating,
        ]);
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
    public function export(Request $request)
    {
        $query = WebVitalMetric::query()
            ->when($request->filled('type'), fn($q) => $q->ofType($request->get('type')))
            ->when($request->filled('rating'), fn($q) => $q->withRating($request->get('rating')))
            ->orderByDesc('measured_at');

        $metrics = $query->get();

        $filename = 'web-vitals-' . now()->format('Y-m-d-His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Session ID', 'URL', 'Metric Type', 'Value', 'Rating', 'Metadata', 'Measured At']);

        foreach ($metrics as $metric) {
            fputcsv($output, [
                $metric->id,
                $metric->session_id,
                $metric->url,
                $metric->metric_type,
                $metric->value,
                $metric->rating,
                json_encode($metric->metadata),
                $metric->measured_at->toDateTimeString(),
            ]);
        }

        fclose($output);
        exit;
    }
}
