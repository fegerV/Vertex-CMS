<?php

namespace App\System\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CacheService
{
    public function clearAll(): array
    {
        $results = [
            'application_cache' => $this->clearApplication(),
            'page_cache' => $this->clearPages(),
            'view_cache' => $this->call('view:clear'),
            'config_cache' => $this->call('config:clear'),
            'route_cache' => $this->call('route:clear'),
        ];

        return $results;
    }

    public function clearApplication(): bool
    {
        return Cache::flush();
    }

    public function clearPages(): bool
    {
        $path = storage_path('vertex-cache/pages');

        if (! File::exists($path)) {
            File::ensureDirectoryExists($path);

            return true;
        }

        File::cleanDirectory($path);

        return true;
    }

    public function status(): array
    {
        $pagePath = storage_path('vertex-cache/pages');

        return [
            'enabled' => (bool) config_value('cache.enabled', true),
            'driver' => config('cache.default'),
            'page_cache_path' => $pagePath,
            'page_cache_writable' => is_writable(dirname($pagePath)) || is_writable($pagePath),
            'page_cache_files' => File::exists($pagePath) ? count(File::allFiles($pagePath)) : 0,
            'settings_key' => config('vertex.cache.settings_key'),
            'menus_key' => config('vertex.cache.menus_key'),
            'seo_key' => config('vertex.cache.seo_key'),
        ];
    }

    private function call(string $command): bool
    {
        Artisan::call($command);

        return true;
    }
}
