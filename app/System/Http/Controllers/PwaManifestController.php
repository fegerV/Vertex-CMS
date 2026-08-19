<?php

namespace App\System\Http\Controllers;

use App\Core\Services\SettingsService;
use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function index(): JsonResponse
    {
        abort_unless((bool) config_value('pwa.enabled', false), 404);

        $icon192 = $this->icon((int) config_value('pwa.icon_192', 0));
        $icon512 = $this->icon((int) config_value('pwa.icon_512', 0));

        return response()->json([
            'name' => config_value('pwa.name', config_value('site.name', 'VertexCMS')),
            'short_name' => config_value('pwa.short_name', 'VertexCMS'),
            'start_url' => config_value('pwa.start_url', '/'),
            'display' => config_value('pwa.display', 'standalone'),
            'theme_color' => config_value('pwa.theme_color', '#020617'),
            'background_color' => config_value('pwa.background_color', '#ffffff'),
            'icons' => array_values(array_filter([$icon192, $icon512])),
        ])->header('Content-Type', 'application/manifest+json');
    }

    private function icon(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }

        $media = Media::query()->find($id);

        if (! $media) {
            return null;
        }

        return [
            'src' => $media->url,
            'sizes' => $media->width && $media->height ? "{$media->width}x{$media->height}" : 'any',
            'type' => $media->mime_type,
        ];
    }
}
