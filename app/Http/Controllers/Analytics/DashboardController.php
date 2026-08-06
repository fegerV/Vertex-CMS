<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Analytics\Dashboard;
use App\Models\Analytics\FunnelStep;
use App\Models\Analytics\Heatmap;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    public function index(): JsonResponse
    {
        $dashboards = Dashboard::with('funnelSteps')->get();
        return response()->json($dashboards);
    }

    public function show(Dashboard $dashboard): JsonResponse
    {
        $dashboard->load('funnelSteps');
        return response()->json($dashboard);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'layout' => 'nullable|array',
            'widgets' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();

        $dashboard = Dashboard::create($validated);

        return response()->json($dashboard, 201);
    }

    public function update(Request $request, Dashboard $dashboard): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'layout' => 'nullable|array',
            'widgets' => 'nullable|array',
            'is_public' => 'boolean',
        ]);

        $dashboard->update($validated);

        return response()->json($dashboard);
    }

    public function destroy(Dashboard $dashboard): JsonResponse
    {
        $dashboard->delete();
        return response()->json(null, 204);
    }

    public function getData(Dashboard $dashboard, Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'segment']);
        
        $data = $this->analyticsService->getDashboardData($dashboard, $filters);

        return response()->json([
            'dashboard' => $dashboard,
            'data' => $data,
        ]);
    }
}
