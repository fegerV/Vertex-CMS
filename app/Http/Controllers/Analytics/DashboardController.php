<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Analytics\Dashboard;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    public function index(): JsonResponse
    {
        $dashboards = Dashboard::query()
            ->with('funnelSteps')
            ->where(fn ($query) => $query
                ->where('user_id', auth()->id())
                ->orWhere('is_public', true))
            ->get();

        return response()->json($dashboards);
    }

    public function show(Dashboard $dashboard): JsonResponse
    {
        $this->authorizeDashboard($dashboard);
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
        $this->authorizeDashboard($dashboard, true);
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
        $this->authorizeDashboard($dashboard, true);
        $dashboard->delete();

        return response()->json(null, 204);
    }

    public function getData(Dashboard $dashboard, Request $request): JsonResponse
    {
        $this->authorizeDashboard($dashboard);
        $filters = $request->only(['date_from', 'date_to', 'segment']);

        $data = $this->analyticsService->getDashboardData($dashboard, $filters);

        return response()->json([
            'dashboard' => $dashboard,
            'data' => $data,
        ]);
    }

    private function authorizeDashboard(Dashboard $dashboard, bool $mustOwn = false): void
    {
        $ownsDashboard = (int) $dashboard->user_id === (int) auth()->id();

        abort_unless($ownsDashboard || (! $mustOwn && $dashboard->is_public), 403);
    }
}
