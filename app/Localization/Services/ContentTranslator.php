<?php

namespace App\Localization\Services;

use InvalidArgumentException;

class ContentTranslator
{
    public function resolve(array $translations, string $locale, ?string $fallback = null): mixed
    {
        $this->assertSupported($locale);
        $fallback ??= (string) config('platform-modules.localization.default', 'ru');

        return $translations[$locale] ?? $translations[$fallback] ?? collect($translations)->first();
    }

    public function localizedUri(string $uri, string $locale): string
    {
        $this->assertSupported($locale);
        $uri = '/'.ltrim($uri, '/');

        return $locale === config('platform-modules.localization.default') ? $uri : "/{$locale}{$uri}";
    }

    private function assertSupported(string $locale): void
    {
        if (! in_array($locale, config('platform-modules.localization.supported', []), true)) {
            throw new InvalidArgumentException("Unsupported locale: {$locale}");
        }
    }
}
