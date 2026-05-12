<?php

namespace App\System\Http\Controllers;

use App\Core\Services\SettingsService;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Support\Api\ApiResponse;
use App\System\Http\Resources\MenuResource;
use App\System\Http\Resources\PublicSiteSettingsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicSettingsApiController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function site(Request $request): JsonResponse
    {
        if (! config_value('api.public_enabled', true)) {
            return ApiResponse::error('api_disabled', 'Public API is disabled.', status: 403);
        }

        return ApiResponse::success(
            PublicSiteSettingsResource::make($this->settings->publicSiteSettings())->resolve($request)
        );
    }

    public function menu(Request $request, string $location): JsonResponse
    {
        if (! config_value('api.public_enabled', true)) {
            return ApiResponse::error('api_disabled', 'Public API is disabled.', status: 403);
        }

        $menuQuery = Menu::query()
            ->where('location', $location)
            ->with(['items' => fn ($q) => $q->orderBy('sort_order')]);

        if (Schema::hasColumn('menus', 'is_active')) {
            $menuQuery->where('is_active', true);
        }

        $menu = $menuQuery->first();

        return ApiResponse::success(
            $menu
                ? MenuResource::make($menu)->resolve($request)
                : [
                    'location' => $location,
                    'items' => [],
                ]
        );
    }
}

