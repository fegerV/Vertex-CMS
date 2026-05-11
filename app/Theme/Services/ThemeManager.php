<?php

namespace App\Theme\Services;

use App\Models\Page;

class ThemeManager
{
    public function activeTheme(): string
    {
        return (string) config('vertex.theme', 'default');
    }

    public function metadata(?string $theme = null): array
    {
        $theme ??= $this->activeTheme();
        $path = base_path("themes/{$theme}/theme.json");

        if (! is_file($path)) {
            return $theme === 'default' ? $this->defaultMetadata() : $this->metadata('default');
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : $this->defaultMetadata();
    }

    public function pageView(?Page $page = null): string
    {
        $template = trim((string) ($page?->template ?: 'page'));
        $theme = $this->activeTheme();

        $candidates = array_values(array_unique([
            "themes.{$theme}.templates.{$template}",
            "themes.{$theme}.page",
            "themes.default.templates.{$template}",
            'themes.default.page',
            'frontend.page',
        ]));

        return $this->firstExistingView($candidates) ?? 'frontend.page';
    }

    public function offlineView(): string
    {
        $theme = $this->activeTheme();

        $candidates = [
            "themes.{$theme}.offline",
            'themes.default.offline',
            'frontend.offline',
        ];

        return $this->firstExistingView($candidates) ?? 'frontend.offline';
    }

    public function termArchiveView(): string
    {
        $theme = $this->activeTheme();

        $candidates = [
            "themes.{$theme}.term-archive",
            'themes.default.term-archive',
            'frontend.term-archive',
        ];

        return $this->firstExistingView($candidates) ?? 'frontend.term-archive';
    }

    public function blockView(string $type): ?string
    {
        $theme = $this->activeTheme();

        $candidates = [
            "themes.{$theme}.blocks.{$type}",
            "themes.default.blocks.{$type}",
        ];

        return $this->firstExistingView($candidates);
    }

    private function firstExistingView(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (view()->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function defaultMetadata(): array
    {
        return [
            'name' => 'Default',
            'slug' => 'default',
            'version' => '0.1.0',
            'core_constraint' => '^0.1',
            'supports' => [
                'pwa' => true,
                'builder' => true,
                'responsive' => true,
            ],
        ];
    }
}
