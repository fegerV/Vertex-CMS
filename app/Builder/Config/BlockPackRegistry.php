<?php

namespace App\Builder\Config;

class BlockPackRegistry
{
    public static function for(string $type): array
    {
        return self::all()[$type] ?? [];
    }

    public static function all(): array
    {
        return [
            'heading' => [
                'typography-pack' => [
                    'label' => 'Typography',
                    'description' => 'Heading copy, semantic level and alignment.',
                    'icon' => 'text',
                    'fields' => ['text', 'level', 'align', 'font_size'],
                ],
                'surface-pack' => [
                    'label' => 'Surface',
                    'description' => 'Color treatment for the heading.',
                    'icon' => 'palette',
                    'fields' => ['color'],
                ],
            ],
            'text' => [
                'typography-pack' => [
                    'label' => 'Typography',
                    'description' => 'Body copy, alignment and readable text scale.',
                    'icon' => 'text',
                    'fields' => ['content', 'align', 'font_size', 'line_height'],
                ],
                'surface-pack' => [
                    'label' => 'Surface',
                    'description' => 'Color treatment for the text block.',
                    'icon' => 'palette',
                    'fields' => ['color'],
                ],
            ],
            'button' => [
                'button-treatment-pack' => [
                    'label' => 'Button treatment',
                    'description' => 'CTA label, destination and visual treatment.',
                    'icon' => 'sparkles',
                    'fields' => ['text', 'url', 'target', 'style', 'size'],
                ],
            ],
            'image' => [
                'media-settings-pack' => [
                    'label' => 'Media settings',
                    'description' => 'Asset selection, fallback source and accessibility.',
                    'icon' => 'image',
                    'fields' => ['media_id', 'url', 'alt'],
                ],
                'surface-pack' => [
                    'label' => 'Surface',
                    'description' => 'Corners and visual treatment around the image.',
                    'icon' => 'palette',
                    'fields' => ['radius', 'shadow'],
                ],
                'spacing-pack' => [
                    'label' => 'Spacing',
                    'description' => 'Frame dimensions for the image block.',
                    'icon' => 'spacing',
                    'fields' => ['width', 'height'],
                ],
            ],
            'video' => [
                'media-settings-pack' => [
                    'label' => 'Media settings',
                    'description' => 'Source type, URL and player framing.',
                    'icon' => 'image',
                    'fields' => ['type', 'url', 'ratio', 'width'],
                ],
                'behavior-pack' => [
                    'label' => 'Behavior',
                    'description' => 'Playback toggles and embed behavior.',
                    'icon' => 'toggle',
                    'fields' => ['autoplay', 'loop', 'muted', 'controls'],
                ],
            ],
            'hero' => [
                'typography-pack' => [
                    'label' => 'Typography',
                    'description' => 'Headline hierarchy and hero copy.',
                    'icon' => 'text',
                    'fields' => ['title', 'subtitle', 'title_color', 'subtitle_color'],
                ],
                'media-settings-pack' => [
                    'label' => 'Media settings',
                    'description' => 'Background asset and hero media surface.',
                    'icon' => 'image',
                    'fields' => ['background'],
                ],
                'button-treatment-pack' => [
                    'label' => 'Button treatment',
                    'description' => 'Hero CTA label, destination and button colors.',
                    'icon' => 'sparkles',
                    'fields' => ['button_text', 'button_url', 'button_target', 'button_bg_color', 'button_text_color', 'button_border_color'],
                ],
                'spacing-pack' => [
                    'label' => 'Spacing',
                    'description' => 'Vertical breathing room inside the hero.',
                    'icon' => 'spacing',
                    'fields' => ['padding_top', 'padding_bottom'],
                ],
            ],
            'list' => [
                'content-pack' => [
                    'label' => 'List content',
                    'description' => 'List items and readable copy.',
                    'icon' => 'list',
                    'fields' => ['items'],
                ],
                'layout-pack' => [
                    'label' => 'List treatment',
                    'description' => 'Marker style and list presentation.',
                    'icon' => 'layers',
                    'fields' => ['type'],
                ],
            ],
            'faq' => [
                'content-pack' => [
                    'label' => 'FAQ content',
                    'description' => 'Question and answer pairs.',
                    'icon' => 'help-circle',
                    'fields' => ['items'],
                ],
            ],
            'icon' => [
                'icon-pack' => [
                    'label' => 'Icon',
                    'description' => 'Glyph, size and visual treatment.',
                    'icon' => 'star',
                    'fields' => ['icon', 'size', 'color', 'background', 'radius'],
                ],
            ],
            'columns' => [
                'layout-pack' => [
                    'label' => 'Column layout',
                    'description' => 'Column count and spacing.',
                    'icon' => 'columns',
                    'fields' => ['count', 'gap'],
                ],
            ],
            'container' => [
                'layout-pack' => [
                    'label' => 'Container layout',
                    'description' => 'Content width and inner spacing.',
                    'icon' => 'box',
                    'fields' => ['max_width', 'padding_top', 'padding_bottom', 'padding_left', 'padding_right'],
                ],
            ],
            'spacer' => [
                'spacing-pack' => [
                    'label' => 'Spacing',
                    'description' => 'Vertical empty space.',
                    'icon' => 'spacing',
                    'fields' => ['height'],
                ],
            ],
            'divider' => [
                'surface-pack' => [
                    'label' => 'Divider style',
                    'description' => 'Line treatment, color and dimensions.',
                    'icon' => 'minus',
                    'fields' => ['style', 'color', 'thickness', 'width'],
                ],
            ],
            'form' => [
                'form-content-pack' => [
                    'label' => 'Form content',
                    'description' => 'Form source, fields and labels.',
                    'icon' => 'form',
                    'fields' => ['mode', 'form_id', 'title', 'description', 'fields', 'submit_label'],
                ],
                'form-behavior-pack' => [
                    'label' => 'Form behavior',
                    'description' => 'Submission, validation and notification settings.',
                    'icon' => 'toggle',
                    'fields' => ['success_message', 'error_message', 'multipage', 'ajax', 'enable_honeypot', 'enable_recaptcha', 'notify_admin', 'notify_user'],
                ],
                'form-style-pack' => [
                    'label' => 'Form style',
                    'description' => 'Theme, layout and label presentation.',
                    'icon' => 'palette',
                    'fields' => ['theme', 'layout', 'label_position', 'show_labels', 'required_mark', 'custom_css'],
                ],
            ],
            'seo-meta' => [
                'seo-pack' => [
                    'label' => 'SEO metadata',
                    'description' => 'Search metadata and indexing hints.',
                    'icon' => 'search',
                    'fields' => ['title', 'description', 'keywords', 'robots', 'canonical'],
                ],
            ],
            'accordion' => [
                'behavior-pack' => [
                    'label' => 'Accordion behavior',
                    'description' => 'Expansion behavior for accordion items.',
                    'icon' => 'toggle',
                    'fields' => ['allow_multiple'],
                ],
            ],
            'tabs' => [
                'layout-pack' => [
                    'label' => 'Tabs layout',
                    'description' => 'Tab treatment and alignment.',
                    'icon' => 'tab',
                    'fields' => ['style', 'alignment'],
                ],
            ],
            'modal' => [
                'content-pack' => [
                    'label' => 'Modal content',
                    'description' => 'Trigger copy and modal body.',
                    'icon' => 'window',
                    'fields' => ['trigger_text', 'title', 'content'],
                ],
                'layout-pack' => [
                    'label' => 'Modal layout',
                    'description' => 'Modal size and presentation.',
                    'icon' => 'layers',
                    'fields' => ['size'],
                ],
            ],
            'tooltip' => [
                'content-pack' => [
                    'label' => 'Tooltip content',
                    'description' => 'Trigger, tooltip copy and placement.',
                    'icon' => 'message-circle',
                    'fields' => ['text', 'content', 'position'],
                ],
            ],
            'alert' => [
                'content-pack' => [
                    'label' => 'Alert content',
                    'description' => 'Alert type, title and message.',
                    'icon' => 'info',
                    'fields' => ['type', 'title', 'content'],
                ],
                'behavior-pack' => [
                    'label' => 'Alert behavior',
                    'description' => 'Dismissal behavior.',
                    'icon' => 'toggle',
                    'fields' => ['closable'],
                ],
            ],
            'progress-bar' => [
                'progress-pack' => [
                    'label' => 'Progress',
                    'description' => 'Value range, color and label visibility.',
                    'icon' => 'loader',
                    'fields' => ['value', 'max', 'color', 'height', 'show_label'],
                ],
            ],
            'breadcrumbs' => [
                'content-pack' => [
                    'label' => 'Breadcrumbs',
                    'description' => 'Breadcrumb separator and trail presentation.',
                    'icon' => 'link',
                    'fields' => ['separator'],
                ],
            ],
            'collapse' => [
                'content-pack' => [
                    'label' => 'Collapse content',
                    'description' => 'Title and default open state.',
                    'icon' => 'chevron-right',
                    'fields' => ['title', 'open'],
                ],
            ],
        ];
    }
}
