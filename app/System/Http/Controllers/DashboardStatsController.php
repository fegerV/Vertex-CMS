<?php

namespace App\System\Http\Controllers;

use App\Analytics\Services\TrafficAnalyticsService;
use App\Ecommerce\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class DashboardStatsController extends Controller
{
    public function __construct(
        private readonly TrafficAnalyticsService $analytics,
    ) {
    }

    /**
     * Compact stats payload for the admin dashboard chart widget.
     */
    public function index(): JsonResponse
    {
        $overview = $this->analytics->overview(30);
        $paidOrders = Order::query()->where('payment_status', 'paid');

        return response()->json([
            'visitors' => (int) ($overview['totals']['visitors'] ?? 0),
            'visits' => (int) ($overview['totals']['visits'] ?? 0),
            'sales' => (int) (clone $paidOrders)->count(),
            'revenue' => round((float) (clone $paidOrders)->sum('total'), 2),
            'published_pages' => Page::query()->where('status', 'published')->count(),
            'trend' => collect($overview['trend'] ?? [])
                ->map(fn ($row) => [
                    'date' => (string) ($row['date'] ?? ''),
                    'visitors' => (int) ($row['visitors'] ?? 0),
                ])
                ->values()
                ->all(),
        ]);
    }
}
