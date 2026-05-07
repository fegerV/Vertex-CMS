<?php

namespace App\System\Http\Controllers;

use App\Core\Services\SettingsService;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;

class PublicSettingsApiController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function site(): JsonResponse
    {
        if (! config_value('api.public_enabled', true)) {
            return response()->json([
                'error' => [
                    'code' => 'api_disabled',
                    'message' => 'Public API is disabled.',
                    'details' => [],
                ],
            ], 403);
        }

        return response()->json([
            'data' => $this->settings->publicSiteSettings(),
            'meta' => [
                'api_version' => config_value('api.version', 'v1'),
            ],
        ]);
    }

    public function menu(string $location): JsonResponse
    {
        if (! config_value('api.public_enabled', true)) {
            return response()->json([
                'error' => [
                    'code' => 'api_disabled',
                    'message' => 'Public API is disabled.',
                    'details' => [],
                ],
            ], 403);
        }

        $menu = Menu::query()
            ->where('location', $location)
            ->where('is_active', true)
            ->with(['items' => fn ($q) => $q->orderBy('sort_order')])
            ->first();

        return response()->json([
            'data' => $menu ? $menu->items->map(fn ($item) => [
                'id' => $item->id,
                'label' => $item->label,
                'url' => $item->url,
                'target' => $item->target,
                'parent_id' => $item->parent_id,
                'sort_order' => $item->sort_order,
            ]) : [],
        ]);
    }
}

