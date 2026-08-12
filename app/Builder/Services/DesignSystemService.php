<?php

namespace App\Builder\Services;

use App\Core\Services\SettingsService;

class DesignSystemService
{
    private const DEFAULTS = [
        'primary' => '#0f766e',
        'secondary' => '#334155',
        'accent' => '#f59e0b',
        'text' => '#0f172a',
        'background' => '#ffffff',
        'heading_font' => 'Manrope',
        'body_font' => 'Manrope',
        'base_font_size' => 16,
        'content_width' => 1200,
        'section_spacing' => 64,
        'button_radius' => 8,
        'button_weight' => 600,
    ];

    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function tokens(): array
    {
        return $this->normalize([
            'primary' => $this->settings->get('design.primary_color'),
            'secondary' => $this->settings->get('design.secondary_color'),
            'accent' => $this->settings->get('design.accent_color'),
            'text' => $this->settings->get('design.text_color'),
            'background' => $this->settings->get('design.background_color'),
            'heading_font' => $this->settings->get('design.heading_font'),
            'body_font' => $this->settings->get('design.body_font'),
            'base_font_size' => $this->settings->get('design.base_font_size'),
            'content_width' => $this->settings->get('design.content_width'),
            'section_spacing' => $this->settings->get('design.section_spacing'),
            'button_radius' => $this->settings->get('design.button_radius'),
            'button_weight' => $this->settings->get('design.button_weight'),
        ]);
    }

    public function normalize(array $values): array
    {
        $tokens = self::DEFAULTS;

        foreach (['primary', 'secondary', 'accent', 'text', 'background'] as $key) {
            if (is_string($values[$key] ?? null) && preg_match('/^#[0-9a-f]{6}$/i', $values[$key])) {
                $tokens[$key] = strtolower($values[$key]);
            }
        }

        foreach (['heading_font', 'body_font'] as $key) {
            $font = trim((string) ($values[$key] ?? ''));
            if ($font !== '' && preg_match('/^[\pL\pN .,_-]{1,80}$/u', $font)) {
                $tokens[$key] = $font;
            }
        }

        $ranges = [
            'base_font_size' => [12, 24],
            'content_width' => [640, 1920],
            'section_spacing' => [0, 200],
            'button_radius' => [0, 100],
            'button_weight' => [100, 900],
        ];

        foreach ($ranges as $key => [$min, $max]) {
            if (is_numeric($values[$key] ?? null)) {
                $tokens[$key] = max($min, min($max, (int) $values[$key]));
            }
        }

        $tokens['palette'] = collect(['primary', 'secondary', 'accent', 'text', 'background'])
            ->map(fn (string $key) => ['name' => $key, 'label' => ucfirst($key), 'value' => $tokens[$key]])
            ->values()
            ->all();

        return $tokens;
    }

    public function css(): string
    {
        $tokens = $this->tokens();

        return sprintf(
            ':root{--vc-primary:%s;--vc-secondary:%s;--vc-accent:%s;--vc-site-text:%s;--vc-site-background:%s;--vc-font-heading:"%s",sans-serif;--vc-font-body:"%s",sans-serif;--vc-font-size-base:%dpx;--vc-container-max-width:%dpx;--vc-section-padding:%dpx;--vc-button-radius:%dpx;--vc-button-weight:%d}',
            $tokens['primary'], $tokens['secondary'], $tokens['accent'], $tokens['text'], $tokens['background'],
            $tokens['heading_font'], $tokens['body_font'], $tokens['base_font_size'], $tokens['content_width'],
            $tokens['section_spacing'], $tokens['button_radius'], $tokens['button_weight'],
        );
    }
}
