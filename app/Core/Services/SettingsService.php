<?php

namespace App\Core\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function all(): array
    {
        return Cache::rememberForever(config('vertex.cache.settings_key'), function (): array {
            return Setting::query()
                ->get()
                ->mapWithKeys(fn (Setting $setting) => [$setting->full_key => $setting->castValue()])
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function forgetCache(): void
    {
        Cache::forget(config('vertex.cache.settings_key'));
    }
}

