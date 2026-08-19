<?php

namespace App\System\Services;

use App\Contracts\CacheInvalidatorContract;
use App\Core\Services\SettingsService;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class CoreCacheInvalidator implements CacheInvalidatorContract
{
    public function __construct(
        private readonly CacheService $cache,
        private readonly SettingsService $settings,
    ) {}

    public function invalidate(string $domain, array $context = []): void
    {
        match ($domain) {
            'settings' => $this->settings->forgetCache(),
            'pages' => $this->cache->clearPages(),
            'menus' => Cache::forget(config('vertex.cache.menus_key')),
            'seo' => Cache::forget(config('vertex.cache.seo_key')),
            'all' => $this->cache->clearAll(),
            default => throw new InvalidArgumentException("Unknown cache domain [{$domain}]."),
        };
    }
}
