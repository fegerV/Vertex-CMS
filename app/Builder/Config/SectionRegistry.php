<?php

namespace App\Builder\Config;

class SectionRegistry
{
    public static function config(): array
    {
        return [
            'tabs' => self::tabs(),
            'default_settings' => self::defaultSettings(),
            'surface_tokens' => self::surfaceTokens(),
            'presets' => self::presets(),
            'capabilities' => self::capabilities(),
            'actions' => self::actions(),
            'commands' => self::commands(),
            'presentation' => self::presentation(),
        ];
    }

    public static function tabs(): array
    {
        return ['content', 'style', 'advanced'];
    }

    public static function defaultSettings(): array
    {
        return [
            'padding_top' => 56,
            'padding_bottom' => 56,
            'background_color' => null,
            'css_class' => null,
        ];
    }

    public static function surfaceTokens(): array
    {
        return [
            ['id' => 'transparent', 'label' => 'Transparent', 'color' => 'transparent'],
            ['id' => 'canvas', 'label' => 'Canvas', 'color' => '#ffffff'],
            ['id' => 'soft', 'label' => 'Soft surface', 'color' => '#f8fafc'],
            ['id' => 'brand', 'label' => 'Brand tint', 'color' => '#ecfeff'],
            ['id' => 'contrast', 'label' => 'Dark surface', 'color' => '#0f172a'],
        ];
    }

    public static function presets(): array
    {
        return [
            [
                'id' => 'hero-surface',
                'label' => 'Hero surface',
                'description' => 'Spacious section with a soft tinted surface for page intros.',
                'settings' => [
                    'background_color' => '#ecfeff',
                    'padding_top' => 88,
                    'padding_bottom' => 88,
                    'css_class' => 'section-hero-surface',
                ],
            ],
            [
                'id' => 'content-default',
                'label' => 'Content default',
                'description' => 'Balanced spacing for general content sections.',
                'settings' => [
                    'background_color' => null,
                    'padding_top' => 56,
                    'padding_bottom' => 56,
                    'css_class' => 'section-content',
                ],
            ],
            [
                'id' => 'compact-stack',
                'label' => 'Compact stack',
                'description' => 'Tighter spacing for dense content sequences.',
                'settings' => [
                    'background_color' => null,
                    'padding_top' => 28,
                    'padding_bottom' => 28,
                    'css_class' => 'section-compact',
                ],
            ],
            [
                'id' => 'dark-contrast',
                'label' => 'Dark contrast',
                'description' => 'High-contrast surface for CTA or footer-like sections.',
                'settings' => [
                    'background_color' => '#0f172a',
                    'padding_top' => 72,
                    'padding_bottom' => 72,
                    'css_class' => 'section-dark',
                ],
            ],
        ];
    }

    public static function capabilities(): array
    {
        return [
            'presets' => true,
            'surface_tokens' => true,
            'spacing' => true,
            'css_class' => true,
        ];
    }

    public static function actions(): array
    {
        return [
            ['id' => 'quick-add', 'label' => 'Add block', 'icon' => 'plus'],
            ['id' => 'move', 'label' => 'Move section', 'icon' => 'drag'],
            ['id' => 'move-up', 'label' => 'Move section up', 'icon' => 'arrow-up'],
            ['id' => 'move-down', 'label' => 'Move section down', 'icon' => 'arrow-down'],
            ['id' => 'duplicate', 'label' => 'Duplicate section', 'icon' => 'duplicate'],
            ['id' => 'delete', 'label' => 'Delete section', 'icon' => 'close', 'tone' => 'danger'],
        ];
    }

    public static function commands(): array
    {
        return [
            ['id' => 'quick-add', 'label' => 'Quick add block', 'description' => 'Open section palette', 'shortcut' => 'A'],
            ['id' => 'duplicate-section', 'label' => 'Duplicate section', 'shortcut' => '[]'],
            ['id' => 'delete-section', 'label' => 'Delete section', 'shortcut' => 'X'],
        ];
    }

    public static function presentation(): array
    {
        return [
            'toolbar' => [
                'style' => 'floating',
                'visibility' => 'hover-or-selected',
            ],
            'selection' => [
                'mode' => 'single',
                'clear_block_selection' => true,
            ],
            'canvas' => [
                'insert_slots' => 'between-blocks',
                'dropzone' => 'section-body',
                'preview' => 'page-section',
            ],
        ];
    }
}
