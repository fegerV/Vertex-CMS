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
        $fields = collect($block['fields'] ?? [])
            ->map(fn (array $field, string $key) => $this->normalizeFieldDefinition($type, $key, $field))
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

    public function normalizeFieldDefinition(string $blockType, string $key, array $field): array
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
                'level' => ['group' => 'content', 'help' => 'Choose the semantic heading level.'],
                'text' => ['group' => 'content', 'help' => 'Main heading copy shown on the page.'],
                'align' => ['group' => 'style', 'help' => 'Align the heading inside its container.'],
                'color' => ['group' => 'style', 'help' => 'Set the heading text color.'],
                'font_size' => ['group' => 'style', 'help' => 'Pick a visual size for the heading.'],
            ],
            'text' => [
                'content' => ['group' => 'content', 'help' => 'Body copy for this text block.'],
                'align' => ['group' => 'style', 'help' => 'Set paragraph alignment.'],
                'color' => ['group' => 'style', 'help' => 'Set the text color.'],
                'font_size' => ['group' => 'style', 'help' => 'Choose a readable text size.'],
            ],
            'list' => [
                'type' => ['group' => 'style', 'help' => 'Choose whether the list is bulleted, numbered or unstyled.'],
                'items' => ['group' => 'content', 'help' => 'Each repeater item becomes one list entry.'],
                'content' => ['group' => 'content', 'help' => 'Text shown inside the list item.'],
            ],
            'faq' => [
                'items' => ['group' => 'content', 'help' => 'Manage the question and answer pairs shown in the FAQ.'],
                'question' => ['group' => 'content', 'help' => 'Question shown in the accordion header.'],
                'answer' => ['group' => 'content', 'help' => 'Answer shown when the FAQ item is expanded.'],
            ],
            'button' => [
                'text' => ['group' => 'content', 'help' => 'Visible call-to-action text.'],
                'url' => ['group' => 'content', 'help' => 'Destination URL for this button.', 'placeholder' => 'https://example.com'],
                'target' => ['group' => 'advanced', 'help' => 'Choose whether to open the link in the same tab or a new one.'],
                'style' => ['group' => 'style', 'help' => 'Select the visual button treatment.'],
                'size' => ['group' => 'style', 'help' => 'Pick a small, medium or large button size.'],
            ],
            'image' => [
                'media_id' => ['group' => 'content', 'help' => 'Pick an item from the media library whenever possible.'],
                'url' => ['group' => 'content', 'help' => 'Fallback direct image URL when the media library is not used.', 'placeholder' => 'https://example.com/image.jpg'],
                'alt' => ['group' => 'content', 'help' => 'Important for accessibility and SEO.'],
                'width' => ['group' => 'style', 'help' => 'CSS width value for the image frame.'],
                'height' => ['group' => 'style', 'help' => 'CSS height value for the image frame.'],
                'radius' => ['group' => 'style', 'help' => 'Rounded corners for the image.'],
                'shadow' => ['group' => 'style', 'help' => 'Optional shadow around the image.'],
            ],
            'gallery' => [
                'images' => ['group' => 'content', 'help' => 'Manage gallery items and their alt text.'],
                'media_id' => ['group' => 'content', 'help' => 'Reference a media library item.'],
                'alt' => ['group' => 'content', 'help' => 'Alt text for the gallery image.'],
                'columns' => ['group' => 'style', 'help' => 'How many gallery columns to render.'],
                'gap' => ['group' => 'style', 'help' => 'Spacing between gallery items.'],
                'radius' => ['group' => 'style', 'help' => 'Rounded corners for each gallery image.'],
                'lightbox' => ['group' => 'advanced', 'help' => 'Open full images in an overlay lightbox.'],
            ],
            'video' => [
                'type' => ['group' => 'content', 'help' => 'Choose the video source type before pasting the URL.'],
                'url' => ['group' => 'content', 'help' => 'Paste the YouTube, Vimeo or direct video URL here.', 'placeholder' => 'https://example.com/video'],
                'ratio' => ['group' => 'style', 'help' => 'Aspect ratio of the embedded player.'],
                'width' => ['group' => 'style', 'help' => 'Optional width override for the video player.'],
                'autoplay' => ['group' => 'advanced', 'help' => 'Start playback automatically when the page loads.'],
                'loop' => ['group' => 'advanced', 'help' => 'Restart the video automatically after it ends.'],
                'muted' => ['group' => 'advanced', 'help' => 'Mute audio for autoplay-safe embeds.'],
                'controls' => ['group' => 'advanced', 'help' => 'Show the playback controls to visitors.'],
            ],
            'icon' => [
                'icon' => ['group' => 'content', 'help' => 'Choose the icon glyph used in this block.'],
                'size' => ['group' => 'style', 'help' => 'Control the visual size of the icon.'],
                'color' => ['group' => 'style', 'help' => 'Primary icon color.'],
                'background' => ['group' => 'style', 'help' => 'Optional background fill behind the icon.'],
                'radius' => ['group' => 'style', 'help' => 'Rounded corners for the icon surface.'],
            ],
            'hero' => [
                'title' => ['group' => 'content', 'help' => 'Main hero headline.'],
                'subtitle' => ['group' => 'content', 'help' => 'Supporting copy below the main title.'],
                'background' => ['group' => 'content', 'help' => 'Background media for the hero surface.'],
                'button_text' => ['group' => 'content', 'help' => 'CTA text shown inside the hero button.'],
                'button_url' => ['group' => 'content', 'help' => 'Destination URL for the hero CTA.', 'placeholder' => 'https://example.com'],
                'button_target' => ['group' => 'advanced', 'help' => 'Open the CTA in the same tab or a new one.'],
                'title_color' => ['group' => 'style', 'help' => 'Hero heading color.'],
                'subtitle_color' => ['group' => 'style', 'help' => 'Hero subtitle color.'],
                'button_bg_color' => ['group' => 'style', 'help' => 'CTA background color.'],
                'button_text_color' => ['group' => 'style', 'help' => 'CTA text color.'],
                'button_border_color' => ['group' => 'style', 'help' => 'Optional CTA border color.'],
                'padding_top' => ['group' => 'style', 'help' => 'Top breathing room inside the hero.'],
                'padding_bottom' => ['group' => 'style', 'help' => 'Bottom breathing room inside the hero.'],
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
