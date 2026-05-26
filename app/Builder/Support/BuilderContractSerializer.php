<?php

namespace App\Builder\Support;

use Illuminate\Support\Str;

class BuilderContractSerializer
{
    public function serializeRegistry(array $registry): array
    {
        return collect($registry)
            ->mapWithKeys(fn (array $block, string $type) => [
                $type => $this->normalizeBlockDefinition($type, $block),
            ])
            ->all();
    }

    public function normalizeBlockDefinition(string $type, array $block): array
    {
        $packRecipes = $this->resolveBlockPackRecipes($type, $block);
        $fields = collect($block['fields'] ?? [])
            ->map(fn (array $field, string $key) => $this->normalizeFieldDefinition($type, $key, $field, $packRecipes))
            ->all();

        $defaultBlock = (array) ($block['default'] ?? []);
        $defaultSettings = (array) ($defaultBlock['settings'] ?? []);
        $editorKind = in_array($type, ['heading', 'text'], true) ? 'rich-text-capable' : 'custom';
        $editorTabs = $this->resolveEditorTabs($type, $fields, $block);
        $fieldTypes = collect($fields)->pluck('type')->filter()->unique()->values()->all();

        return array_merge($block, [
            'type' => $type,
            'name' => $this->normalizeBlockName($type, $block),
            'description' => $this->normalizeBlockDescription($type, $block),
            'fields' => $fields,
            'editor' => array_merge((array) ($block['editor'] ?? []), [
                'component' => 'vc-builder-block-' . Str::kebab($type),
                'kind' => $editorKind,
                'supports' => $fieldTypes,
                'tabs' => $editorTabs,
                'inspector' => [
                    'groups' => $editorTabs,
                    'default_tab' => $editorTabs[0] ?? 'content',
                ],
                'preview' => [
                    'badge' => $this->resolveBlockBadge($type),
                    'empty_state' => $this->resolveBlockEmptyState($type),
                ],
                'packs' => $packRecipes,
                'quick_add' => [
                    'hint' => $this->resolveQuickAddHint($type),
                    'keywords' => $this->resolveQuickAddKeywords($type),
                ],
                'inline_editing' => $this->resolveInlineEditing($type),
                'capabilities' => [
                    'move' => true,
                    'duplicate' => true,
                    'delete' => true,
                    'quick_add' => true,
                    'presets' => ! empty($fields),
                    'multi_select' => true,
                    'media' => in_array('media', $fieldTypes, true) || array_key_exists('media_id', $fields),
                ],
                'presentation' => $this->resolveBlockPresentation($type),
                'actions' => $this->resolveBlockActions($type),
                'commands' => $this->resolveBlockCommands($type),
            ]),
            'default_block' => [
                'type' => $type,
                'settings' => $defaultSettings,
            ],
        ]);
    }

    public function normalizeFieldDefinition(string $blockType, string $key, array $field, array $packRecipes = []): array
    {
        if (($field['type'] ?? null) === 'select' && is_array($field['options'] ?? null)) {
            $field['options'] = collect($field['options'])
                ->map(fn ($label, $value) => [
                    'value' => (string) $value,
                    'label' => $this->normalizeFieldLabel((string) $value, (string) $label),
                ])
                ->values()
                ->all();
        }

        if (($field['type'] ?? null) === 'repeater' && is_array($field['fields'] ?? null)) {
            $field['fields'] = collect($field['fields'])
                ->map(function (array $nestedField, int $index) use ($blockType): array {
                    $nestedKey = (string) ($nestedField['key'] ?? 'item_' . $index);
                    return $this->normalizeFieldDefinition($blockType, $nestedKey, $nestedField);
                })
                ->values()
                ->all();
        }

        $overrides = $this->editorFieldOverrides($blockType, $key);

        $field['label'] = $overrides['label']
            ?? $this->normalizeFieldLabel($key, (string) ($field['label'] ?? ''));
        $field['help'] = $field['help'] ?? $overrides['help'] ?? $this->normalizeHelpText($field['help'] ?? null);
        $field['placeholder'] = $field['placeholder'] ?? $overrides['placeholder'] ?? $this->normalizePlaceholder($key, $field['placeholder'] ?? null);
        $field['group'] = $field['group'] ?? $overrides['group'] ?? $this->inferFieldGroup($key, $field);
        $field['priority'] = $field['priority'] ?? $overrides['priority'] ?? $this->inferFieldPriority($key, $field);
        $field['importance'] = $field['importance'] ?? $overrides['importance'] ?? $this->inferFieldImportance($field);
        $field['layout'] = array_merge(
            $this->inferFieldLayout($key, $field),
            (array) ($field['layout'] ?? []),
            (array) ($overrides['layout'] ?? [])
        );
        $field['control'] = array_merge(
            $this->inferFieldControl($key, $field),
            (array) ($field['control'] ?? []),
            (array) ($overrides['control'] ?? [])
        );
        $field['control'] = $this->normalizeFieldControlMetadata($key, $field['control'], $packRecipes);

        return $field;
    }

    private function normalizeBlockName(string $type, array $block): string
    {
        $name = (string) ($block['name'] ?? '');

        if ($name !== '' && ! $this->looksCorruptedText($name)) {
            return $name;
        }

        return match ($type) {
            'heading' => 'Heading',
            'text' => 'Text',
            'list' => 'List',
            'faq' => 'FAQ',
            'button' => 'Button',
            'image' => 'Image',
            'video' => 'Video',
            'gallery' => 'Gallery',
            'icon' => 'Icon',
            'hero' => 'Hero',
            'columns' => 'Columns',
            'container' => 'Container',
            'spacer' => 'Spacer',
            'divider' => 'Divider',
            'news-feed' => 'News feed',
            'testimonials' => 'Testimonials',
            'counter' => 'Counter',
            default => Str::of($type)->replace(['-', '_'], ' ')->title()->toString(),
        };
    }

    private function normalizeBlockDescription(string $type, array $block): string
    {
        $description = (string) ($block['description'] ?? '');

        if ($description !== '' && ! $this->looksCorruptedText($description)) {
            return $description;
        }

        return match ($type) {
            'heading' => 'Primary page or section heading.',
            'text' => 'Text block with formatting support.',
            'list' => 'Bullet or numbered list.',
            'faq' => 'Frequently asked questions block.',
            'button' => 'Button with link and action settings.',
            'image' => 'Image block with media and display settings.',
            'video' => 'Embedded video block for YouTube, Vimeo or HTML5.',
            'gallery' => 'Image grid with optional lightbox.',
            'icon' => 'Single icon with color and surface controls.',
            'hero' => 'Large hero banner with title, copy and CTA.',
            'columns' => 'Multi-column layout container.',
            'container' => 'Content wrapper with max width and padding.',
            'spacer' => 'Empty spacing block.',
            'divider' => 'Horizontal divider line.',
            'news-feed' => 'Dynamic feed of recent posts.',
            'testimonials' => 'Customer testimonials layout.',
            'counter' => 'Animated numeric counter.',
            default => 'Reusable builder block.',
        };
    }

    private function normalizeFieldLabel(string $key, ?string $label): string
    {
        $label = (string) ($label ?? '');

        if ($label !== '' && ! $this->looksCorruptedText($label)) {
            return $label;
        }

        return match ($key) {
            'media_id' => 'Media item',
            'url' => 'URL',
            'alt' => 'Alt text',
            'align' => 'Alignment',
            'color' => 'Color',
            'background' => 'Background',
            'background_color' => 'Background color',
            'font_size' => 'Font size',
            'font_weight' => 'Font weight',
            'line_height' => 'Line height',
            'padding_top' => 'Top padding',
            'padding_bottom' => 'Bottom padding',
            'padding_left' => 'Left padding',
            'padding_right' => 'Right padding',
            'max_width' => 'Max width',
            'button_text' => 'Button text',
            'button_url' => 'Button URL',
            'button_target' => 'Button target',
            'button_bg_color' => 'Button background',
            'button_text_color' => 'Button text color',
            'button_border_color' => 'Button border color',
            'show_image' => 'Show image',
            'show_excerpt' => 'Show excerpt',
            'show_date' => 'Show date',
            'css_class' => 'CSS class',
            default => Str::of($key)->replace(['-', '_'], ' ')->title()->toString(),
        };
    }

    private function normalizeHelpText(mixed $help): ?string
    {
        if (! is_string($help) || trim($help) === '') {
            return null;
        }

        return $this->looksCorruptedText($help) ? null : $help;
    }

    private function normalizePlaceholder(string $key, mixed $placeholder): ?string
    {
        if (is_string($placeholder) && trim($placeholder) !== '' && ! $this->looksCorruptedText($placeholder)) {
            return $placeholder;
        }

        return match ($key) {
            'css_class' => 'for example hero-section',
            'url', 'button_url' => 'https://example.com',
            default => is_string($placeholder) && trim($placeholder) !== '' ? null : null,
        };
    }

    private function inferFieldGroup(string $key, array $field): string
    {
        $normalizedKey = Str::lower($key);
        $type = (string) ($field['type'] ?? '');

        if ($type === 'media' || $normalizedKey === 'media_id') {
            return 'content';
        }

        foreach (['class', 'id', 'target', 'rel', 'loading', 'anchor', 'role', 'schema'] as $needle) {
            if (str_contains($normalizedKey, $needle)) {
                return 'advanced';
            }
        }

        foreach (['color', 'background', 'align', 'size', 'width', 'height', 'padding', 'margin', 'gap', 'radius', 'shadow', 'border', 'font', 'opacity'] as $needle) {
            if (str_contains($normalizedKey, $needle)) {
                return 'style';
            }
        }

        return 'content';
    }

    private function inferFieldPriority(string $key, array $field): int
    {
        $group = (string) ($field['group'] ?? 'content');
        $normalizedKey = Str::lower($key);

        $base = match ($group) {
            'content' => 100,
            'style' => 500,
            'advanced' => 800,
            default => 400,
        };

        return $base + match (true) {
            in_array($normalizedKey, ['text', 'content', 'title', 'subtitle', 'items', 'media_id', 'url', 'images'], true) => 0,
            in_array($normalizedKey, ['level', 'alt', 'button_text', 'button_url', 'type', 'icon'], true) => 10,
            str_contains($normalizedKey, 'color') => 30,
            str_contains($normalizedKey, 'padding') || str_contains($normalizedKey, 'margin') || str_contains($normalizedKey, 'gap') => 40,
            str_contains($normalizedKey, 'class') || str_contains($normalizedKey, 'target') || str_contains($normalizedKey, 'loading') => 60,
            default => 90,
        };
    }

    private function inferFieldImportance(array $field): string
    {
        $priority = (int) ($field['priority'] ?? 999);

        if ($priority <= 110) {
            return 'primary';
        }

        return 'secondary';
    }

    private function inferFieldLayout(string $key, array $field): array
    {
        $type = (string) ($field['type'] ?? 'text');
        $normalizedKey = Str::lower($key);

        if ($type === 'media' || $normalizedKey === 'media_id') {
            return [
                'variant' => 'media',
                'span' => 'full',
            ];
        }

        if ($type === 'repeater' || $type === 'textarea') {
            return [
                'variant' => 'stacked',
                'span' => 'full',
            ];
        }

        if ($type === 'toggle') {
            return [
                'variant' => 'compact',
                'span' => 'half',
            ];
        }

        if (in_array($type, ['select', 'number', 'color'], true)) {
            return [
                'variant' => 'compact',
                'span' => 'half',
            ];
        }

        if (str_contains($normalizedKey, 'color') || str_contains($normalizedKey, 'padding') || str_contains($normalizedKey, 'margin') || str_contains($normalizedKey, 'gap')) {
            return [
                'variant' => 'compact',
                'span' => 'half',
            ];
        }

        return [
            'variant' => 'stacked',
            'span' => 'full',
        ];
    }

    private function inferFieldControl(string $key, array $field): array
    {
        $type = (string) ($field['type'] ?? 'text');
        $normalizedKey = Str::lower($key);
        $options = array_values((array) ($field['options'] ?? []));

        if ($type === 'media' || $normalizedKey === 'media_id') {
            return [
                'variant' => 'media-picker-inline',
                'family' => 'media',
                'family_label' => 'Media',
                'family_icon' => 'image',
            ];
        }

        if ($type === 'toggle') {
            return [
                'variant' => 'toggle',
                'family' => 'behavior',
                'family_label' => 'Behavior',
                'family_icon' => 'toggle',
            ];
        }

        if ($type === 'color' || str_contains($normalizedKey, 'color') || $normalizedKey === 'background') {
            return [
                'variant' => 'color-swatch',
                'family' => $this->inferControlFamily($normalizedKey, 'color-swatch'),
                'family_label' => $this->inferControlFamilyLabel($normalizedKey, 'color-swatch'),
                'family_icon' => $this->inferControlFamilyIcon($normalizedKey, 'color-swatch'),
            ];
        }

        if (
            str_contains($normalizedKey, 'padding')
            || str_contains($normalizedKey, 'margin')
            || str_contains($normalizedKey, 'gap')
            || str_contains($normalizedKey, 'radius')
            || str_contains($normalizedKey, 'font_size')
            || str_contains($normalizedKey, 'line_height')
            || str_contains($normalizedKey, 'width')
            || str_contains($normalizedKey, 'height')
        ) {
            return [
                'variant' => 'spacing-slider',
                'min' => $this->inferSpacingMin($normalizedKey),
                'max' => $this->inferSpacingMax($normalizedKey),
                'step' => $this->inferSpacingStep($normalizedKey),
                'unit' => 'px',
                'family' => $this->inferControlFamily($normalizedKey, 'spacing-slider'),
                'family_label' => $this->inferControlFamilyLabel($normalizedKey, 'spacing-slider'),
                'family_icon' => $this->inferControlFamilyIcon($normalizedKey, 'spacing-slider'),
            ];
        }

        if ($type === 'select' && count($options) > 0 && count($options) <= 4) {
            return [
                'variant' => 'segmented-select',
                'family' => $this->inferControlFamily($normalizedKey, 'segmented-select'),
                'family_label' => $this->inferControlFamilyLabel($normalizedKey, 'segmented-select'),
                'family_icon' => $this->inferControlFamilyIcon($normalizedKey, 'segmented-select'),
            ];
        }

        if (in_array($normalizedKey, ['url', 'button_url'], true)) {
            return [
                'variant' => 'link-composer',
                'family' => 'link',
                'family_label' => 'Link',
                'family_icon' => 'link',
            ];
        }

        return [
            'variant' => 'default',
        ];
    }

    private function normalizeFieldControlMetadata(string $key, array $control, array $packRecipes = []): array
    {
        $normalizedKey = Str::lower($key);
        $variant = (string) ($control['variant'] ?? 'default');
        $recipe = $this->findPackRecipeForField($normalizedKey, $packRecipes);

        if ($recipe !== null) {
            $control['pack'] = $control['pack'] ?? $recipe['id'];
            $control['pack_label'] = $control['pack_label'] ?? $recipe['label'] ?? null;
            $control['pack_description'] = $control['pack_description'] ?? $recipe['description'] ?? null;
            $control['pack_icon'] = $control['pack_icon'] ?? $recipe['icon'] ?? null;
        }

        if (! isset($control['family'])) {
            $control['family'] = $this->inferControlFamily($normalizedKey, $variant);
        }

        if (! isset($control['family_label'])) {
            $control['family_label'] = $this->inferControlFamilyLabel($normalizedKey, $variant);
        }

        if (! isset($control['family_icon'])) {
            $control['family_icon'] = $this->inferControlFamilyIcon($normalizedKey, $variant);
        }

        if (! isset($control['pack'])) {
            $control['pack'] = $this->inferControlPack($normalizedKey, $variant, (string) ($control['family'] ?? 'content'));
        }

        if (! isset($control['pack_label'])) {
            $control['pack_label'] = $this->inferControlPackLabel((string) ($control['pack'] ?? 'content-pack'));
        }

        if (! isset($control['pack_description'])) {
            $control['pack_description'] = $this->inferControlPackDescription((string) ($control['pack'] ?? 'content-pack'));
        }

        if (! isset($control['pack_icon'])) {
            $control['pack_icon'] = $this->inferControlPackIcon((string) ($control['pack'] ?? 'content-pack'));
        }

        return $control;
    }

    private function findPackRecipeForField(string $key, array $packRecipes): ?array
    {
        foreach ($packRecipes as $recipe) {
            $fields = array_map(fn ($field) => Str::lower((string) $field), (array) ($recipe['fields'] ?? []));
            if (in_array($key, $fields, true)) {
                return $recipe;
            }
        }

        return null;
    }

    private function resolveBlockPackRecipes(string $type, array $block): array
    {
        $recipes = collect((array) data_get($block, 'editor.packs', []))
            ->map(function (array $recipe, string|int $key): array {
                return [
                    'id' => (string) ($recipe['id'] ?? $key),
                    'label' => (string) ($recipe['label'] ?? 'Pack'),
                    'description' => (string) ($recipe['description'] ?? ''),
                    'icon' => (string) ($recipe['icon'] ?? 'layers'),
                    'fields' => array_values(array_map(fn ($field) => (string) $field, (array) ($recipe['fields'] ?? []))),
                ];
            })
            ->keyBy('id')
            ->all();

        return $recipes;
    }

    private function inferControlFamily(string $key, string $variant): string
    {
        return match (true) {
            $variant === 'media-picker-inline' => 'media',
            $variant === 'link-composer' => 'link',
            $variant === 'toggle' => 'behavior',
            str_contains($key, 'font') || str_contains($key, 'line_height') || in_array($key, ['align', 'level'], true) => 'typography',
            str_contains($key, 'color') || $key === 'background' || str_contains($key, 'radius') || str_contains($key, 'shadow') || str_contains($key, 'border') => 'surface',
            str_contains($key, 'padding') || str_contains($key, 'margin') || str_contains($key, 'gap') || str_contains($key, 'width') || str_contains($key, 'height') || str_contains($key, 'max_width') => 'spacing',
            in_array($key, ['style', 'size'], true) => 'appearance',
            in_array($key, ['type', 'ratio', 'target'], true) => 'behavior',
            default => 'content',
        };
    }

    private function inferControlFamilyLabel(string $key, string $variant): string
    {
        return match ($this->inferControlFamily($key, $variant)) {
            'media' => 'Media',
            'link' => 'Link',
            'behavior' => 'Behavior',
            'typography' => 'Typography',
            'surface' => 'Surface',
            'spacing' => 'Spacing',
            'appearance' => 'Appearance',
            default => 'Content',
        };
    }

    private function inferControlFamilyIcon(string $key, string $variant): string
    {
        return match ($this->inferControlFamily($key, $variant)) {
            'media' => 'image',
            'link' => 'link',
            'behavior' => 'toggle',
            'typography' => 'text',
            'surface' => 'palette',
            'spacing' => 'spacing',
            'appearance' => 'sparkles',
            default => 'layers',
        };
    }

    private function inferControlPack(string $key, string $variant, string $family): string
    {
        return match (true) {
            in_array($key, ['text', 'content', 'title', 'subtitle', 'level', 'align', 'font_size', 'font_weight', 'line_height'], true) => 'typography-pack',
            in_array($key, ['button_text', 'button_url', 'button_target', 'button_bg_color', 'button_text_color', 'button_border_color', 'style', 'size'], true) => 'button-treatment-pack',
            in_array($key, ['media_id', 'images', 'background', 'url', 'alt', 'ratio', 'type'], true) && in_array($family, ['media', 'link', 'behavior', 'content'], true) => 'media-settings-pack',
            str_contains($key, 'padding') || str_contains($key, 'margin') || str_contains($key, 'gap') || str_contains($key, 'width') || str_contains($key, 'height') || str_contains($key, 'max_width') => 'spacing-pack',
            in_array($family, ['surface'], true) => 'surface-pack',
            in_array($family, ['typography'], true) => 'typography-pack',
            in_array($family, ['media'], true) => 'media-settings-pack',
            in_array($family, ['link'], true) => 'link-pack',
            in_array($family, ['behavior'], true) => 'behavior-pack',
            default => 'content-pack',
        };
    }

    private function inferControlPackLabel(string $pack): string
    {
        return match ($pack) {
            'typography-pack' => 'Typography',
            'surface-pack' => 'Surface',
            'spacing-pack' => 'Spacing',
            'button-treatment-pack' => 'Button treatment',
            'media-settings-pack' => 'Media settings',
            'link-pack' => 'Link settings',
            'behavior-pack' => 'Behavior',
            default => 'Content',
        };
    }

    private function inferControlPackDescription(string $pack): string
    {
        return match ($pack) {
            'typography-pack' => 'Copy hierarchy, alignment and text scale.',
            'surface-pack' => 'Color, corners and visual surface treatments.',
            'spacing-pack' => 'Dimensions, spacing and layout breathing room.',
            'button-treatment-pack' => 'Call-to-action label, destination and visual treatment.',
            'media-settings-pack' => 'Assets, embeds and media-specific display options.',
            'link-pack' => 'Navigation destination and link behavior.',
            'behavior-pack' => 'Runtime behavior and interaction toggles.',
            default => 'Primary content and semantic structure.',
        };
    }

    private function inferControlPackIcon(string $pack): string
    {
        return match ($pack) {
            'typography-pack' => 'text',
            'surface-pack' => 'palette',
            'spacing-pack' => 'spacing',
            'button-treatment-pack' => 'sparkles',
            'media-settings-pack' => 'image',
            'link-pack' => 'link',
            'behavior-pack' => 'toggle',
            default => 'layers',
        };
    }

    private function inferSpacingMin(string $key): int
    {
        return match (true) {
            str_contains($key, 'line_height') => 1,
            default => 0,
        };
    }

    private function inferSpacingMax(string $key): int
    {
        return match (true) {
            str_contains($key, 'width'), str_contains($key, 'height'), str_contains($key, 'max_width') => 1600,
            str_contains($key, 'font_size') => 120,
            str_contains($key, 'line_height') => 4,
            default => 240,
        };
    }

    private function inferSpacingStep(string $key): int|float
    {
        return match (true) {
            str_contains($key, 'width'), str_contains($key, 'height'), str_contains($key, 'max_width') => 10,
            str_contains($key, 'line_height') => 0.1,
            default => 1,
        };
    }

    private function resolveEditorTabs(string $type, array $fields, array $block): array
    {
        $declaredTabs = collect((array) data_get($block, 'editor.tabs', []))
            ->map(fn ($tab) => Str::lower((string) $tab))
            ->filter(fn (string $tab) => in_array($tab, ['content', 'style', 'advanced'], true))
            ->values();

        if ($declaredTabs->isNotEmpty()) {
            return $declaredTabs->all();
        }

        $groups = collect($fields)
            ->map(fn (array $field) => Str::lower((string) ($field['group'] ?? 'content')))
            ->filter(fn (string $group) => in_array($group, ['content', 'style', 'advanced'], true))
            ->unique()
            ->values();

        $preferredOrder = collect(['content', 'style', 'advanced'])
            ->filter(fn (string $group) => $groups->contains($group))
            ->values();

        if ($preferredOrder->isNotEmpty()) {
            return $preferredOrder->all();
        }

        return in_array($type, ['spacer', 'divider'], true)
            ? ['style', 'advanced']
            : ['content', 'style', 'advanced'];
    }

    private function resolveBlockBadge(string $type): string
    {
        return match ($type) {
            'heading' => 'H',
            'text' => 'T',
            'button' => 'B',
            'image' => 'I',
            'video' => 'V',
            'gallery' => 'G',
            'columns' => '2C',
            'container' => 'BX',
            'faq' => '?',
            'form', 'form-embed' => 'F',
            'icon' => '*',
            'hero' => 'HR',
            'spacer' => 'SP',
            'divider' => 'DV',
            default => Str::upper(Str::substr((string) $type, 0, 2)),
        };
    }

    private function resolveBlockEmptyState(string $type): array
    {
        return match ($type) {
            'image' => [
                'title' => 'Image placeholder',
                'description' => 'Choose a media item or provide a direct image URL to render this block.',
            ],
            'video' => [
                'title' => 'Video placeholder',
                'description' => 'Paste a YouTube, Vimeo or HTML5 video URL to preview this embed.',
            ],
            'gallery' => [
                'title' => 'Gallery placeholder',
                'description' => 'Add one or more media items to build the gallery grid.',
            ],
            'faq' => [
                'title' => 'FAQ placeholder',
                'description' => 'Add question and answer pairs to render the accordion.',
            ],
            'hero' => [
                'title' => 'Hero placeholder',
                'description' => 'Add a headline, supporting copy and background media for this section.',
            ],
            default => [
                'title' => 'Block placeholder',
                'description' => 'Configure this block in the inspector to see a richer preview.',
            ],
        };
    }

    private function resolveQuickAddHint(string $type): string
    {
        return match ($type) {
            'heading' => 'Headline or section title',
            'text' => 'Paragraph, intro or supporting copy',
            'button' => 'Call to action with link',
            'image' => 'Single media item with alt text',
            'video' => 'Embedded video player',
            'gallery' => 'Image grid with lightbox',
            'faq' => 'Accordion of questions and answers',
            'hero' => 'Large intro banner with CTA',
            'columns' => 'Multi-column layout scaffold',
            'container' => 'Wrapper with width and spacing controls',
            default => 'Reusable builder block',
        };
    }

    private function resolveQuickAddKeywords(string $type): array
    {
        return match ($type) {
            'heading' => ['title', 'headline', 'h1', 'section'],
            'text' => ['copy', 'paragraph', 'body', 'intro'],
            'button' => ['cta', 'link', 'action', 'conversion'],
            'image' => ['photo', 'media', 'visual', 'picture'],
            'video' => ['youtube', 'vimeo', 'embed', 'player'],
            'gallery' => ['grid', 'photos', 'lightbox', 'portfolio'],
            'faq' => ['accordion', 'questions', 'answers', 'help'],
            'hero' => ['banner', 'cover', 'landing', 'intro'],
            'columns' => ['layout', 'grid', 'split', 'two-column'],
            'container' => ['wrapper', 'shell', 'content width', 'section'],
            default => [Str::replace('-', ' ', $type)],
        };
    }

    private function resolveBlockActions(string $type): array
    {
        return [
            ['id' => 'move-up', 'label' => 'Move block up', 'icon' => 'arrow-up'],
            ['id' => 'move-down', 'label' => 'Move block down', 'icon' => 'arrow-down'],
            ['id' => 'duplicate', 'label' => 'Duplicate block', 'icon' => 'duplicate'],
            ['id' => 'delete', 'label' => 'Delete block', 'icon' => 'close', 'tone' => 'danger'],
        ];
    }

    private function resolveBlockCommands(string $type): array
    {
        $commands = [
            ['id' => 'duplicate-block', 'label' => 'Duplicate block', 'shortcut' => 'D'],
            ['id' => 'delete-block', 'label' => 'Delete block', 'shortcut' => 'Delete'],
            ['id' => 'move-block-up', 'label' => 'Move block up', 'shortcut' => 'Alt+Up'],
            ['id' => 'move-block-down', 'label' => 'Move block down', 'shortcut' => 'Alt+Down'],
        ];

        $inlineEditing = $this->resolveInlineEditing($type);
        if ($inlineEditing['enabled'] ?? false) {
            array_unshift($commands, [
                'id' => 'inline-edit',
                'label' => $inlineEditing['label'] ?? 'Edit block content',
                'description' => $inlineEditing['description'] ?? 'Open the primary content controls for this block.',
                'shortcut' => $inlineEditing['shortcut'] ?? 'Enter',
            ]);
        }

        return $commands;
    }

    private function resolveBlockPresentation(string $type): array
    {
        return [
            'toolbar' => [
                'style' => 'floating',
                'visibility' => 'hover-or-selected',
            ],
            'selection' => [
                'mode' => in_array($type, ['heading', 'text', 'list', 'button', 'faq', 'icon'], true) ? 'multi' : 'single',
                'focus' => 'inspector',
            ],
            'canvas' => [
                'preview' => in_array($type, ['hero', 'image', 'video', 'gallery'], true) ? 'visual' : 'card',
                'insert_slots' => 'between-blocks',
            ],
        ];
    }

    private function resolveInlineEditing(string $type): array
    {
        return match ($type) {
            'heading' => [
                'enabled' => true,
                'trigger' => 'double-click',
                'target_tab' => 'content',
                'fields' => ['text', 'level'],
                'label' => 'Edit heading',
                'description' => 'Jump straight to the heading copy controls.',
                'shortcut' => 'Enter',
            ],
            'text' => [
                'enabled' => true,
                'trigger' => 'double-click',
                'target_tab' => 'content',
                'fields' => ['content'],
                'label' => 'Edit text',
                'description' => 'Open the main text content field.',
                'shortcut' => 'Enter',
            ],
            'button' => [
                'enabled' => true,
                'trigger' => 'double-click',
                'target_tab' => 'content',
                'fields' => ['text', 'url'],
                'label' => 'Edit button',
                'description' => 'Update the call to action text and destination.',
                'shortcut' => 'Enter',
            ],
            'faq' => [
                'enabled' => true,
                'trigger' => 'double-click',
                'target_tab' => 'content',
                'fields' => ['items'],
                'label' => 'Edit FAQ',
                'description' => 'Manage the FAQ items for this block.',
                'shortcut' => 'Enter',
            ],
            'list' => [
                'enabled' => true,
                'trigger' => 'double-click',
                'target_tab' => 'content',
                'fields' => ['items'],
                'label' => 'Edit list',
                'description' => 'Manage the list items in the inspector.',
                'shortcut' => 'Enter',
            ],
            'image' => [
                'enabled' => true,
                'trigger' => 'double-click',
                'target_tab' => 'content',
                'fields' => ['media_id', 'alt'],
                'label' => 'Edit image',
                'description' => 'Choose media and update alternative text.',
                'shortcut' => 'Enter',
            ],
            'video' => [
                'enabled' => true,
                'trigger' => 'double-click',
                'target_tab' => 'content',
                'fields' => ['url'],
                'label' => 'Edit video',
                'description' => 'Update the video URL and source settings.',
                'shortcut' => 'Enter',
            ],
            'gallery' => [
                'enabled' => true,
                'trigger' => 'double-click',
                'target_tab' => 'content',
                'fields' => ['images'],
                'label' => 'Edit gallery',
                'description' => 'Manage gallery images and display order.',
                'shortcut' => 'Enter',
            ],
            default => [
                'enabled' => false,
                'trigger' => null,
                'target_tab' => 'content',
                'fields' => [],
            ],
        };
    }

    private function looksCorruptedText(string $value): bool
    {
        return str_contains($value, 'Р ')
            || str_contains($value, 'РЎ')
            || str_contains($value, 'Рѓ')
            || str_contains($value, 'РІР‚')
            || str_contains($value, 'РІ')
            || str_contains($value, 'Р“');
    }

    private function editorFieldOverrides(string $blockType, string $key): array
    {
        $map = [
            'heading' => [
                'level' => ['group' => 'content', 'help' => 'Choose the semantic heading level.', 'priority' => 110, 'layout' => ['row' => 'heading-structure'], 'control' => ['variant' => 'segmented-select']],
                'text' => ['group' => 'content', 'help' => 'Main heading copy shown on the page.', 'priority' => 100, 'importance' => 'primary'],
                'align' => ['group' => 'style', 'help' => 'Align the heading inside its container.', 'layout' => ['row' => 'heading-structure']],
                'color' => ['group' => 'style', 'help' => 'Set the heading text color.', 'layout' => ['row' => 'heading-appearance']],
                'font_size' => ['group' => 'style', 'help' => 'Pick a visual size for the heading.', 'layout' => ['row' => 'heading-appearance']],
            ],
            'text' => [
                'content' => ['group' => 'content', 'help' => 'Body copy for this text block.', 'priority' => 100, 'importance' => 'primary'],
                'align' => ['group' => 'style', 'help' => 'Set paragraph alignment.', 'layout' => ['row' => 'text-appearance']],
                'color' => ['group' => 'style', 'help' => 'Set the text color.', 'layout' => ['row' => 'text-appearance']],
                'font_size' => ['group' => 'style', 'help' => 'Choose a readable text size.', 'layout' => ['row' => 'text-scale']],
            ],
            'list' => [
                'type' => ['group' => 'style', 'help' => 'Choose whether the list is bulleted, numbered or unstyled.', 'layout' => ['row' => 'list-structure']],
                'items' => ['group' => 'content', 'help' => 'Each repeater item becomes one list entry.', 'priority' => 100, 'importance' => 'primary'],
                'content' => ['group' => 'content', 'help' => 'Text shown inside the list item.'],
            ],
            'faq' => [
                'items' => ['group' => 'content', 'help' => 'Manage the question and answer pairs shown in the FAQ.', 'priority' => 100, 'importance' => 'primary'],
                'question' => ['group' => 'content', 'help' => 'Question shown in the accordion header.'],
                'answer' => ['group' => 'content', 'help' => 'Answer shown when the FAQ item is expanded.'],
            ],
            'button' => [
                'text' => ['group' => 'content', 'help' => 'Visible call-to-action text.', 'priority' => 100, 'importance' => 'primary'],
                'url' => ['group' => 'content', 'help' => 'Destination URL for this button.', 'placeholder' => 'https://example.com', 'priority' => 110, 'importance' => 'primary', 'control' => ['variant' => 'link-composer']],
                'target' => ['group' => 'advanced', 'help' => 'Choose whether to open the link in the same tab or a new one.', 'control' => ['variant' => 'segmented-select']],
                'style' => ['group' => 'style', 'help' => 'Select the visual button treatment.', 'layout' => ['row' => 'button-appearance'], 'control' => ['variant' => 'segmented-select']],
                'size' => ['group' => 'style', 'help' => 'Pick a small, medium or large button size.', 'layout' => ['row' => 'button-appearance'], 'control' => ['variant' => 'segmented-select']],
            ],
            'image' => [
                'media_id' => ['group' => 'content', 'help' => 'Pick an item from the media library whenever possible.', 'priority' => 100, 'importance' => 'primary'],
                'url' => ['group' => 'content', 'help' => 'Fallback direct image URL when the media library is not used.', 'placeholder' => 'https://example.com/image.jpg', 'priority' => 110, 'control' => ['variant' => 'link-composer']],
                'alt' => ['group' => 'content', 'help' => 'Important for accessibility and SEO.', 'priority' => 120, 'importance' => 'primary'],
                'width' => ['group' => 'style', 'help' => 'CSS width value for the image frame.', 'layout' => ['row' => 'image-dimensions']],
                'height' => ['group' => 'style', 'help' => 'CSS height value for the image frame.', 'layout' => ['row' => 'image-dimensions']],
                'radius' => ['group' => 'style', 'help' => 'Rounded corners for the image.', 'layout' => ['row' => 'image-surface']],
                'shadow' => ['group' => 'style', 'help' => 'Optional shadow around the image.', 'layout' => ['row' => 'image-surface']],
            ],
            'gallery' => [
                'images' => ['group' => 'content', 'help' => 'Add images from the media library, reorder them and tune captions.', 'priority' => 100, 'importance' => 'primary'],
                'media_id' => ['group' => 'content', 'help' => 'Reference a media library item.', 'control' => ['variant' => 'media-picker-inline']],
                'url' => ['group' => 'content', 'help' => 'Fallback direct image URL when the media library is not used.', 'placeholder' => 'https://example.com/image.jpg'],
                'alt' => ['group' => 'content', 'help' => 'Alt text for the gallery image.'],
                'caption' => ['group' => 'content', 'help' => 'Optional visible image caption.'],
                'link' => ['group' => 'content', 'help' => 'Optional click-through URL when lightbox is disabled.', 'placeholder' => 'https://example.com', 'control' => ['variant' => 'link-composer']],
                'layout' => ['group' => 'style', 'help' => 'Choose grid, masonry, slider or carousel rendering.', 'priority' => 110, 'layout' => ['row' => 'gallery-layout'], 'control' => ['variant' => 'segmented-select']],
                'columns' => ['group' => 'style', 'help' => 'How many gallery columns to render on desktop.', 'layout' => ['row' => 'gallery-grid'], 'control' => ['variant' => 'segmented-select']],
                'tablet_columns' => ['group' => 'style', 'help' => 'Columns for tablet-sized screens.', 'layout' => ['row' => 'gallery-responsive'], 'control' => ['variant' => 'segmented-select']],
                'mobile_columns' => ['group' => 'style', 'help' => 'Columns for mobile screens.', 'layout' => ['row' => 'gallery-responsive'], 'control' => ['variant' => 'segmented-select']],
                'gap' => ['group' => 'style', 'help' => 'Spacing between gallery items.', 'layout' => ['row' => 'gallery-grid'], 'control' => ['variant' => 'segmented-select']],
                'radius' => ['group' => 'style', 'help' => 'Rounded corners for each gallery image.', 'layout' => ['row' => 'gallery-surface'], 'control' => ['variant' => 'segmented-select']],
                'aspect_ratio' => ['group' => 'style', 'help' => 'Frame ratio used by grid and slider items.', 'layout' => ['row' => 'gallery-frame'], 'control' => ['variant' => 'segmented-select']],
                'object_fit' => ['group' => 'style', 'help' => 'Crop or fit images inside the frame.', 'layout' => ['row' => 'gallery-frame'], 'control' => ['variant' => 'segmented-select']],
                'caption_mode' => ['group' => 'style', 'help' => 'Show captions as overlays, below images or not at all.', 'layout' => ['row' => 'gallery-caption'], 'control' => ['variant' => 'segmented-select']],
                'lightbox' => ['group' => 'advanced', 'help' => 'Open full images in an overlay lightbox.', 'layout' => ['row' => 'gallery-lightbox']],
                'lightbox_effect' => ['group' => 'advanced', 'help' => 'Animation style for the lightbox overlay.', 'layout' => ['row' => 'gallery-lightbox'], 'control' => ['variant' => 'segmented-select']],
                'show_arrows' => ['group' => 'advanced', 'help' => 'Show previous and next controls for slider layouts.', 'layout' => ['row' => 'gallery-slider']],
                'show_dots' => ['group' => 'advanced', 'help' => 'Show slide position dots for slider layouts.', 'layout' => ['row' => 'gallery-slider']],
                'autoplay' => ['group' => 'advanced', 'help' => 'Automatically advance slider layouts.', 'layout' => ['row' => 'gallery-autoplay']],
                'interval' => ['group' => 'advanced', 'help' => 'Autoplay interval in milliseconds.', 'layout' => ['row' => 'gallery-autoplay']],
            ],
            'video' => [
                'type' => ['group' => 'content', 'help' => 'Choose the video source type before pasting the URL.', 'priority' => 100, 'layout' => ['row' => 'video-source'], 'control' => ['variant' => 'segmented-select']],
                'url' => ['group' => 'content', 'help' => 'Paste the YouTube, Vimeo or direct video URL here.', 'placeholder' => 'https://example.com/video', 'priority' => 110, 'importance' => 'primary', 'control' => ['variant' => 'link-composer']],
                'ratio' => ['group' => 'style', 'help' => 'Aspect ratio of the embedded player.', 'layout' => ['row' => 'video-frame'], 'control' => ['variant' => 'segmented-select']],
                'width' => ['group' => 'style', 'help' => 'Optional width override for the video player.', 'layout' => ['row' => 'video-frame']],
                'autoplay' => ['group' => 'advanced', 'help' => 'Start playback automatically when the page loads.', 'layout' => ['row' => 'video-behavior']],
                'loop' => ['group' => 'advanced', 'help' => 'Restart the video automatically after it ends.', 'layout' => ['row' => 'video-behavior']],
                'muted' => ['group' => 'advanced', 'help' => 'Mute audio for autoplay-safe embeds.', 'layout' => ['row' => 'video-behavior']],
                'controls' => ['group' => 'advanced', 'help' => 'Show the playback controls to visitors.', 'layout' => ['row' => 'video-behavior']],
            ],
            'icon' => [
                'icon' => ['group' => 'content', 'help' => 'Choose the icon glyph used in this block.'],
                'size' => ['group' => 'style', 'help' => 'Control the visual size of the icon.'],
                'color' => ['group' => 'style', 'help' => 'Primary icon color.'],
                'background' => ['group' => 'style', 'help' => 'Optional background fill behind the icon.'],
                'radius' => ['group' => 'style', 'help' => 'Rounded corners for the icon surface.'],
            ],
            'hero' => [
                'title' => ['group' => 'content', 'help' => 'Main hero headline.', 'priority' => 100, 'importance' => 'primary'],
                'subtitle' => ['group' => 'content', 'help' => 'Supporting copy below the main title.', 'priority' => 110, 'importance' => 'primary'],
                'background' => ['group' => 'content', 'help' => 'Background media for the hero surface.'],
                'button_text' => ['group' => 'content', 'help' => 'CTA text shown inside the hero button.', 'priority' => 120, 'layout' => ['row' => 'hero-cta']],
                'button_url' => ['group' => 'content', 'help' => 'Destination URL for the hero CTA.', 'placeholder' => 'https://example.com', 'priority' => 130, 'layout' => ['row' => 'hero-cta'], 'control' => ['variant' => 'link-composer']],
                'button_target' => ['group' => 'advanced', 'help' => 'Open the CTA in the same tab or a new one.', 'control' => ['variant' => 'segmented-select']],
                'title_color' => ['group' => 'style', 'help' => 'Hero heading color.', 'layout' => ['row' => 'hero-color']],
                'subtitle_color' => ['group' => 'style', 'help' => 'Hero subtitle color.', 'layout' => ['row' => 'hero-color']],
                'button_bg_color' => ['group' => 'style', 'help' => 'CTA background color.', 'layout' => ['row' => 'hero-button-style']],
                'button_text_color' => ['group' => 'style', 'help' => 'CTA text color.', 'layout' => ['row' => 'hero-button-style']],
                'button_border_color' => ['group' => 'style', 'help' => 'Optional CTA border color.', 'layout' => ['row' => 'hero-button-style']],
                'padding_top' => ['group' => 'style', 'help' => 'Top breathing room inside the hero.', 'layout' => ['row' => 'hero-spacing']],
                'padding_bottom' => ['group' => 'style', 'help' => 'Bottom breathing room inside the hero.', 'layout' => ['row' => 'hero-spacing']],
            ],
            'columns' => [
                'count' => ['group' => 'content', 'help' => 'How many columns should the layout create.'],
                'gap' => ['group' => 'style', 'help' => 'Spacing between columns.'],
            ],
            'container' => [
                'max_width' => ['group' => 'style', 'help' => 'Limit the readable width of the content container.'],
                'padding_top' => ['group' => 'style', 'help' => 'Top inner padding for the container.'],
                'padding_bottom' => ['group' => 'style', 'help' => 'Bottom inner padding for the container.'],
                'padding_left' => ['group' => 'style', 'help' => 'Left inner padding for the container.'],
                'padding_right' => ['group' => 'style', 'help' => 'Right inner padding for the container.'],
            ],
            'spacer' => [
                'height' => ['group' => 'style', 'help' => 'Vertical empty space created by this spacer.'],
                'css_class' => ['group' => 'advanced', 'help' => 'Optional CSS hook for custom styling.'],
            ],
            'divider' => [
                'style' => ['group' => 'style', 'help' => 'Visual style of the divider line.'],
                'color' => ['group' => 'style', 'help' => 'Divider line color.'],
                'margin_top' => ['group' => 'style', 'help' => 'Space above the divider.'],
                'margin_bottom' => ['group' => 'style', 'help' => 'Space below the divider.'],
                'css_class' => ['group' => 'advanced', 'help' => 'Optional CSS hook for custom styling.'],
            ],
        ];

        return $map[$blockType][$key] ?? [];
    }
}
