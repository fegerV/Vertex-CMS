<?php

namespace App\System\Http\Controllers;

use App\Core\Services\SettingsService;
use App\Http\Controllers\Controller;
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
}

