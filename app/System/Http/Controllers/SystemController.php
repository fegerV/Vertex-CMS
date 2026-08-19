<?php

namespace App\System\Http\Controllers;

use App\Analytics\Services\TrafficAnalyticsService;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\System\Services\ActivityLogService;
use App\System\Services\CacheService;
use App\System\Services\SystemInfoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function __construct(
        private readonly SystemInfoService $systemInfo,
        private readonly CacheService $cache,
        private readonly ActivityLogService $activityLog,
        private readonly TrafficAnalyticsService $analytics,
    ) {
    }

    public function info(): View
    {
        return view('admin.system.info', [
            'info' => $this->systemInfo->get(),
        ]);
    }

    public function logs(Request $request): View
    {
        return view('admin.system.logs', [
            'logs' => ActivityLog::query()
                ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
                ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
                ->latest('created_at')
                ->paginate(50)
                ->withQueryString(),
            'filters' => $request->only(['action', 'user_id']),
        ]);
    }

    public function analytics(Request $request): View
    {
        $days = (int) $request->integer('days', 30);

        return view('admin.system.analytics', [
            'analytics' => $this->analytics->overview($days),
        ]);
    }

    public function cache(): View
    {
        return view('admin.system.cache', [
            'status' => $this->cache->status(),
        ]);
    }

    public function clearCache(Request $request): RedirectResponse
    {
        $scope = $request->validate([
            'scope' => ['required', 'string', 'in:all,application,pages'],
        ])['scope'];

        $result = match ($scope) {
            'application' => ['application_cache' => $this->cache->clearApplication()],
            'pages' => ['page_cache' => $this->cache->clearPages()],
            default => $this->cache->clearAll(),
        };

        $this->activityLog->record('cache.clear', 'cache', null, "Cache scope \"{$scope}\" cleared.", [
            'scope' => $scope,
            'result' => $result,
        ], $request);

        return redirect()
            ->route('admin.system.cache')
            ->with('status', 'Кеш очищен.');
    }
}
