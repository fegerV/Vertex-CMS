<?php

namespace Vertex\Forms;

use Illuminate\Support\Str;

class FieldTypeRegistry
{
    protected const REGISTRY_VERSION = '1.0';

    protected const FIELD_TYPES = [
        'text' => [
            'label' => 'Text Input',
            'category' => 'basic',
            'icon' => 'type-text',
            'description' => 'Single-line text input.',
            'defaults' => [
                'label' => 'Text Field',
                'placeholder' => '',
                'default_value' => '',
                'maxlength' => 255,
                'minlength' => null,
                'help_text' => '',
                'css_class' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true, 'pattern' => '/^[a-z_][a-z0-9_]*$/i'],
                'placeholder' => ['type' => 'string'],
                'default_value' => ['type' => 'string'],
                'maxlength' => ['type' => 'integer', 'min' => 1, 'max' => 255],
                'minlength' => ['type' => 'integer', 'min' => 0],
                'help_text' => ['type' => 'string'],
                'css_class' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => ['string', 'max:255'],
            'editor' => [
                'component' => 'vc-form-field-text',
                'tabs' => [
                    'field' => ['label', 'name', 'placeholder', 'help_text'],
                    'validation' => ['required', 'minlength', 'maxlength'],
                    'logic' => ['conditional'],
                    'appearance' => ['css_class'],
                    'advanced' => ['default_value', 'visible'],
                ],
            ],
        ],
        'email' => [
            'label' => 'Email',
            'category' => 'basic',
            'icon' => 'mail',
            'description' => 'Email input with validation.',
            'defaults' => [
                'label' => 'Email',
                'placeholder' => '',
                'default_value' => '',
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true, 'pattern' => '/^[a-z_][a-z0-9_]*$/i'],
                'placeholder' => ['type' => 'string'],
                'default_value' => ['type' => 'string'],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => ['email'],
            'editor' => [
                'component' => 'vc-form-field-email',
                'tabs' => [
                    'field' => ['label', 'name', 'placeholder', 'help_text'],
                    'validation' => ['required'],
                    'logic' => ['conditional'],
                    'appearance' => [],
                    'advanced' => ['default_value', 'visible'],
                ],
            ],
        ],
        'tel' => [
            'label' => 'Phone Number',
            'category' => 'basic',
            'icon' => 'phone',
            'description' => 'Telephone input.',
            'defaults' => [
                'label' => 'Phone Number',
                'placeholder' => '',
                'default_value' => '',
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'placeholder' => ['type' => 'string'],
                'default_value' => ['type' => 'string'],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => ['regex:/^[\d\+\-\(\) ]+$/'],
            'editor' => [
                'component' => 'vc-form-field-tel',
                'tabs' => [
                    'field' => ['label', 'name', 'placeholder', 'help_text'],
                    'validation' => ['required'],
                    'logic' => ['conditional'],
                    'appearance' => [],
                    'advanced' => ['default_value', 'visible'],
                ],
            ],
        ],
        'number' => [
            'label' => 'Number',
            'category' => 'basic',
            'icon' => 'hash',
            'description' => 'Numeric input.',
            'defaults' => [
                'label' => 'Number',
                'placeholder' => '',
                'default_value' => null,
                'min' => null,
                'max' => null,
                'step' => 1,
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'placeholder' => ['type' => 'string'],
                'default_value' => ['type' => 'number'],
                'min' => ['type' => 'number'],
                'max' => ['type' => 'number'],
                'step' => ['type' => 'number', 'default' => 1],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => ['numeric'],
            'editor' => [
                'component' => 'vc-form-field-number',
                'tabs' => [
                    'field' => ['label', 'name', 'placeholder', 'help_text'],
                    'validation' => ['required', 'min', 'max', 'step'],
                    'logic' => ['conditional'],
                    'appearance' => [],
                    'advanced' => ['default_value', 'visible'],
                ],
            ],
        ],
        'textarea' => [
            'label' => 'Textarea',
            'category' => 'basic',
            'icon' => 'file-text',
            'description' => 'Multi-line text input.',
            'defaults' => [
                'label' => 'Text Area',
                'placeholder' => '',
                'default_value' => '',
                'rows' => 4,
                'minlength' => null,
                'maxlength' => null,
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'placeholder' => ['type' => 'string'],
                'default_value' => ['type' => 'string'],
                'rows' => ['type' => 'integer', 'default' => 4],
                'minlength' => ['type' => 'integer'],
                'maxlength' => ['type' => 'integer'],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => ['string'],
            'editor' => [
                'component' => 'vc-form-field-textarea',
                'tabs' => [
                    'field' => ['label', 'name', 'placeholder', 'help_text'],
                    'validation' => ['required', 'rows', 'minlength', 'maxlength'],
                    'logic' => ['conditional'],
                    'appearance' => [],
                    'advanced' => ['default_value', 'visible'],
                ],
            ],
        ],
        'select' => [
            'label' => 'Dropdown Select',
            'category' => 'choice',
            'icon' => 'chevron-down',
            'description' => 'Single-choice dropdown.',
            'defaults' => [
                'label' => 'Dropdown',
                'placeholder' => 'Select...',
                'choices' => [],
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'placeholder' => ['type' => 'string', 'default' => 'Select...'],
                'choices' => ['type' => 'array'],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-select',
                'tabs' => [
                    'field' => ['label', 'name', 'placeholder', 'help_text'],
                    'validation' => ['required'],
                    'logic' => ['conditional'],
                    'appearance' => [],
                    'advanced' => ['choices', 'visible'],
                ],
            ],
        ],
        'radio' => [
            'label' => 'Radio Buttons',
            'category' => 'choice',
            'icon' => 'circle',
            'description' => 'Single-choice radio group.',
            'defaults' => [
                'label' => 'Radio Group',
                'choices' => [],
                'inline' => false,
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'choices' => ['type' => 'array'],
                'inline' => ['type' => 'boolean', 'default' => false],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-radio',
                'tabs' => [
                    'field' => ['label', 'name', 'help_text'],
                    'validation' => ['required'],
                    'logic' => ['conditional'],
                    'appearance' => ['inline'],
                    'advanced' => ['choices', 'visible'],
                ],
            ],
        ],
        'checkbox' => [
            'label' => 'Single Checkbox',
            'category' => 'choice',
            'icon' => 'check-square',
            'description' => 'Single agreement or boolean field.',
            'defaults' => [
                'label' => 'Checkbox',
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-checkbox',
                'tabs' => [
                    'field' => ['label', 'name', 'help_text'],
                    'validation' => ['required'],
                    'logic' => ['conditional'],
                    'appearance' => [],
                    'advanced' => ['visible'],
                ],
            ],
        ],
        'checkbox_group' => [
            'label' => 'Checkbox Group',
            'category' => 'choice',
            'icon' => 'check-square',
            'description' => 'Multi-choice checkbox group.',
            'defaults' => [
                'label' => 'Checkbox Group',
                'choices' => [],
                'inline' => false,
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'choices' => ['type' => 'array'],
                'inline' => ['type' => 'boolean', 'default' => false],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-checkbox-group',
                'tabs' => [
                    'field' => ['label', 'name', 'help_text'],
                    'validation' => ['required'],
                    'logic' => ['conditional'],
                    'appearance' => ['inline'],
                    'advanced' => ['choices', 'visible'],
                ],
            ],
        ],
        'file' => [
            'label' => 'File Upload',
            'category' => 'advanced',
            'icon' => 'upload',
            'description' => 'Upload a file attachment.',
            'defaults' => [
                'label' => 'File Upload',
                'max_size' => 10240,
                'mime_types' => ['image/jpeg', 'image/png', 'application/pdf'],
                'multiple' => false,
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'max_size' => ['type' => 'integer', 'default' => 10240],
                'mime_types' => ['type' => 'array'],
                'multiple' => ['type' => 'boolean', 'default' => false],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => ['file'],
            'editor' => [
                'component' => 'vc-form-field-file',
                'tabs' => [
                    'field' => ['label', 'name', 'help_text'],
                    'validation' => ['required', 'max_size', 'mime_types', 'multiple'],
                    'logic' => ['conditional'],
                    'appearance' => [],
                    'advanced' => ['visible'],
                ],
            ],
        ],
        'date' => [
            'label' => 'Date',
            'category' => 'basic',
            'icon' => 'calendar',
            'description' => 'Date picker field.',
            'defaults' => [
                'label' => 'Date',
                'min' => null,
                'max' => null,
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'min' => ['type' => 'string', 'format' => 'date'],
                'max' => ['type' => 'string', 'format' => 'date'],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-date',
                'tabs' => [
                    'field' => ['label', 'name', 'help_text'],
                    'validation' => ['required', 'min', 'max'],
                    'logic' => ['conditional'],
                    'appearance' => [],
                    'advanced' => ['visible'],
                ],
            ],
        ],
        'hidden' => [
            'label' => 'Hidden Field',
            'category' => 'hidden',
            'icon' => 'eye-off',
            'description' => 'Hidden system value.',
            'defaults' => [
                'label' => 'Hidden Field',
                'default_value' => '',
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'default_value' => ['type' => 'string'],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-hidden',
                'tabs' => [
                    'field' => ['label', 'name'],
                    'validation' => [],
                    'logic' => [],
                    'appearance' => [],
                    'advanced' => ['default_value', 'visible'],
                ],
            ],
        ],
        'calculator' => [
            'label' => 'Calculator Field',
            'category' => 'advanced',
            'icon' => 'calculator',
            'description' => 'Computed field using formula rules.',
            'defaults' => [
                'label' => 'Calculator',
                'formula' => '',
                'depends_on' => [],
                'prefix' => '',
                'suffix' => '',
                'precision' => 2,
                'live' => true,
                'readonly' => true,
                'help_text' => '',
                'required' => false,
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'name' => ['type' => 'string', 'required' => true],
                'formula' => ['type' => 'string', 'required' => true],
                'depends_on' => ['type' => 'array'],
                'prefix' => ['type' => 'string'],
                'suffix' => ['type' => 'string'],
                'precision' => ['type' => 'integer', 'min' => 0, 'max' => 10, 'default' => 2],
                'live' => ['type' => 'boolean', 'default' => true],
                'readonly' => ['type' => 'boolean', 'default' => true],
                'help_text' => ['type' => 'string'],
                'required' => ['type' => 'boolean', 'default' => false],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-calculator',
                'tabs' => [
                    'field' => ['label', 'name', 'help_text'],
                    'validation' => ['required', 'formula', 'depends_on', 'precision'],
                    'logic' => ['conditional'],
                    'appearance' => ['prefix', 'suffix'],
                    'advanced' => ['readonly', 'live', 'visible'],
                ],
            ],
        ],
        'heading' => [
            'label' => 'Section Heading',
            'category' => 'layout',
            'icon' => 'heading',
            'description' => 'Static heading content.',
            'defaults' => [
                'label' => 'Section Heading',
                'level' => 'h2',
                'align' => 'left',
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'level' => ['type' => 'select', 'options' => ['h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4'], 'default' => 'h2'],
                'align' => ['type' => 'select', 'options' => ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'], 'default' => 'left'],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-heading',
                'tabs' => [
                    'field' => ['label'],
                    'validation' => [],
                    'logic' => [],
                    'appearance' => ['level', 'align'],
                    'advanced' => ['visible'],
                ],
            ],
        ],
        'divider' => [
            'label' => 'Divider',
            'category' => 'layout',
            'icon' => 'minus',
            'description' => 'Visual separator between groups.',
            'defaults' => [
                'label' => 'Divider',
                'style' => 'solid',
                'margin' => '16px',
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'style' => ['type' => 'select', 'options' => ['solid' => 'Solid', 'dashed' => 'Dashed', 'dotted' => 'Dotted'], 'default' => 'solid'],
                'margin' => ['type' => 'string', 'default' => '16px'],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-divider',
                'tabs' => [
                    'field' => ['label'],
                    'validation' => [],
                    'logic' => [],
                    'appearance' => ['style', 'margin'],
                    'advanced' => ['visible'],
                ],
            ],
        ],
        'html' => [
            'label' => 'Custom HTML',
            'category' => 'advanced',
            'icon' => 'code',
            'description' => 'Static HTML block.',
            'defaults' => [
                'label' => 'Custom HTML',
                'content' => '',
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'content' => ['type' => 'string', 'required' => true],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-html',
                'tabs' => [
                    'field' => ['label', 'content'],
                    'validation' => [],
                    'logic' => [],
                    'appearance' => [],
                    'advanced' => ['visible'],
                ],
            ],
        ],
        'page_break' => [
            'label' => 'Page Break',
            'category' => 'layout',
            'icon' => 'file-plus',
            'description' => 'Start a new step/page in the form.',
            'defaults' => [
                'label' => 'Page Break',
                'page_title' => '',
                'visible' => true,
            ],
            'props' => [
                'label' => ['type' => 'string', 'required' => true],
                'page_title' => ['type' => 'string', 'default' => ''],
                'visible' => ['type' => 'boolean', 'default' => true],
            ],
            'validation' => [],
            'editor' => [
                'component' => 'vc-form-field-page-break',
                'tabs' => [
                    'field' => ['label', 'page_title'],
                    'validation' => [],
                    'logic' => [],
                    'appearance' => [],
                    'advanced' => ['visible'],
                ],
            ],
        ],
    ];

    public static function registryVersion(): string
    {
        return self::REGISTRY_VERSION;
    }

    public static function getSchema(string $type): ?array
    {
        return self::FIELD_TYPES[$type] ?? null;
    }

    public static function createDefault(string $type, array $overrides = []): array
    {
        $schema = self::getSchema($type);

        if ($schema === null) {
            throw new \InvalidArgumentException("Unknown field type: {$type}");
        }

        $defaults = $schema['defaults'] ?? [];
        $label = $overrides['label'] ?? $defaults['label'] ?? $schema['label'];
        $name = $overrides['name'] ?? Str::snake($type . '_' . Str::random(6));

        return array_merge([
            'id' => 'fld_' . Str::lower(Str::random(10)),
            'type' => $type,
            'name' => $name,
            'label' => $label,
            'sort_order' => 0,
            'required' => false,
            'visible' => true,
            'options' => [],
        ], $defaults, $overrides);
    }

    public static function getAll(): array
    {
        return collect(self::FIELD_TYPES)
            ->map(fn (array $schema, string $type) => self::toRegistryItem($type, $schema))
            ->values()
            ->all();
    }

    public static function getByCategory(): array
    {
        $grouped = [];

        foreach (self::getAll() as $field) {
            $grouped[$field['category']][] = $field;
        }

        return $grouped;
    }

    public static function getRegistryPayload(): array
    {
        return [
            'registry_version' => self::registryVersion(),
            'fields' => self::getAll(),
            'categories' => collect(self::getByCategory())
                ->map(fn (array $fields, string $category) => [
                    'id' => $category,
                    'label' => Str::headline(str_replace('_', ' ', $category)),
                    'count' => count($fields),
                ])
                ->values()
                ->all(),
            'count' => count(self::FIELD_TYPES),
        ];
    }

    protected static function toRegistryItem(string $type, array $schema): array
    {
        $defaults = self::createDefault($type, [
            'name' => Str::snake($type . '_field'),
        ]);

        return [
            'type' => $type,
            'label' => $schema['label'],
            'category' => $schema['category'] ?? 'other',
            'icon' => $schema['icon'] ?? 'circle',
            'description' => $schema['description'] ?? null,
            'props' => $schema['props'] ?? [],
            'validation' => $schema['validation'] ?? [],
            'editor' => array_merge([
                'component' => 'vc-form-field-' . Str::kebab($type),
                'tabs' => [],
            ], $schema['editor'] ?? []),
            'default_field' => $defaults,
        ];
    }
}
