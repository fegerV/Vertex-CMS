<?php

namespace App\System\Http\Controllers;

use App\Http\Controllers\Controller;
use App\System\Services\CacheService;
use App\System\Services\SystemInfoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemApiController extends Controller
{
    public function __construct(
        private readonly SystemInfoService $systemInfo,
        private readonly CacheService $cache,
    ) {
    }

    public function info(): JsonResponse
    {
        return response()->json(['data' => $this->systemInfo->get()]);
    }

    public function clearCache(Request $request): JsonResponse
    {
        $scope = $request->validate([
            'scope' => ['nullable', 'string', 'in:all,application,pages'],
        ])['scope'] ?? 'all';

        $result = match ($scope) {
            'application' => ['application_cache' => $this->cache->clearApplication()],
            'pages' => ['page_cache' => $this->cache->clearPages()],
            default => $this->cache->clearAll(),
        };

        return response()->json(['ok' => true, 'data' => $result]);
    }
}
