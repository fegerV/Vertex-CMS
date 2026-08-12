<?php

namespace App\Core\Services;

use App\Contracts\SettingsRepositoryContract;
use App\Core\Support\SettingCatalog;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService implements SettingsRepositoryContract
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

    public function allMasked(): array
    {
        $all = $this->all();

        foreach (SettingCatalog::secretKeys() as $key) {
            $all[$key] = filled($all[$key] ?? null) ? '********' : '';
        }

        return $all;
    }

    public function setMany(array $values): void
    {
        foreach ($values as $fullKey => $value) {
            $definition = SettingCatalog::definition($fullKey);

            if (! $definition) {
                continue;
            }

            [$group, $key] = explode('.', $fullKey, 2);

            if (($definition['secret'] ?? false) && blank($value)) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['group_name' => $group, 'setting_key' => $key],
                [
                    'setting_value' => $this->serializeValue($value, $definition['type'] ?? 'string'),
                    'type' => $definition['type'] ?? 'string',
                    'autoload' => true,
                ],
            );
        }

        $this->forgetCache();
    }

    public function publicSiteSettings(): array
    {
        $all = $this->all();

        return collect(SettingCatalog::publicSiteKeys())
            ->reduce(function (array $carry, string $key) use ($all): array {
                [$group, $setting] = explode('.', $key, 2);
                $carry[$group] ??= [];
                $carry[$group][$setting] = $all[$key] ?? $this->defaultPublicValue($key);

                return $carry;
            }, []);
    }

    public function forgetCache(): void
    {
        Cache::forget(config('vertex.cache.settings_key'));
    }

    private function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) ((int) $value),
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE),
            'encrypted' => encrypt((string) $value),
            default => (string) $value,
        };
    }

    private function defaultPublicValue(string $key): mixed
    {
        return match ($key) {
            'site.name' => config('app.name', 'VertexCMS'),
            'site.url' => config('app.url'),
            'site.locale' => config('app.locale', 'en'),
            'api.public_enabled' => true,
            'api.mobile_enabled' => false,
            'api.version' => 'v1',
            'pwa.enabled' => false,
            'pwa.name' => config('app.name', 'VertexCMS'),
            'pwa.short_name' => config('app.name', 'VertexCMS'),
            'pwa.theme_color' => '#020617',
            'pwa.background_color' => '#ffffff',
            'pwa.display' => 'standalone',
            'pwa.start_url' => '/',
            default => null,
        };
    }
}
