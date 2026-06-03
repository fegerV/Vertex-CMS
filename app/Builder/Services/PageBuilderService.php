<?php

namespace App\Builder\Services;

use App\Builder\Config\BlockRegistry;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\SeoMeta;
use Illuminate\Support\Str;

class PageBuilderService
{
    // Управление версиями
    public function createRevision(Page $page, array $content, string $action = 'auto-save'): PageRevision
    {
        return PageRevision::create([
            'page_id' => $page->id,
            'user_id' => auth()->id(),
            'title' => $page->title,
            'content_json' => $this->normalizeContentSnapshot($page, $content),
            'custom_fields_json' => $page->custom_fields_json ?? [],
            'seo_json' => $page->seoMeta?->toArray() ?? [],
            'action' => $action,
            'created_at' => now(),
        ]);
    }

    public function getRevisions(Page $page)
    {
        return PageRevision::where('page_id', $page->id)
            ->latest()
            ->paginate(20);
    }

    public function restoreRevision(Page $page, PageRevision $revision): Page
    {
        $content = $this->normalizeContentSnapshot($page, $revision->content_json ?? []);

        $page->forceFill([
            'content_json' => $content,
            'custom_fields_json' => $revision->custom_fields_json ?? $page->custom_fields_json,
            'title' => $revision->title,
        ])->save();

        $seoPayload = is_array($revision->seo_json ?? null) ? $revision->seo_json : [];
        if ($seoPayload !== []) {
            SeoMeta::query()->updateOrCreate(
                [
                    'entity_type' => Page::class,
                    'entity_id' => $page->id,
                ],
                [
                    'title' => $seoPayload['title'] ?? null,
                    'description' => $seoPayload['description'] ?? null,
                    'canonical_url' => $seoPayload['canonical_url'] ?? null,
                    'robots' => $seoPayload['robots'] ?? 'index, follow',
                    'og_title' => $seoPayload['og_title'] ?? null,
                    'og_description' => $seoPayload['og_description'] ?? null,
                    'og_image' => $seoPayload['og_image'] ?? null,
                    'schema_json' => is_array($seoPayload['schema_json'] ?? null) ? $seoPayload['schema_json'] : null,
                    'include_in_sitemap' => (bool) ($seoPayload['include_in_sitemap'] ?? false),
                ],
            );
        }

        $page->load('seoMeta');

        return $page;
    }

    // Работа с блоками
    public function compileBlock(string $type, array $settings): string
    {
        $block = BlockRegistry::get($type);
        
        if (!$block) {
            return '<!-- Unknown block -->';
        }

        // Используем Blade шаблон, если есть
        $template = $block['template'] ?? 'builder.blocks.' . $type;

        if (view()->exists($template)) {
            return view($template, ['settings' => $settings])->render();
        }

        // Fallback к базовому рендерингу
        return $this->renderBlockFallback($type, $settings);
    }

    protected function renderBlockFallback(string $type, array $settings): string
    {
        // Базовая реализация рендеринга
        return match ($type) {
            'heading' => sprintf(
                '<%1$s class="vc-heading" style="color:%2$s;text-align:%3$s;font-size:%4$s">%5$s</%1$s>',
                $settings['level'] ?? 'h2',
                $settings['color'] ?? '#111827',
                $settings['align'] ?? 'left',
                $settings['font_size'] ?? '1.5rem',
                e($settings['text'] ?? '')
            ),
            'text' => sprintf(
                '<div class="vc-text" style="color:%s;text-align:%s">%s</div>',
                $settings['color'] ?? '#374151',
                $settings['align'] ?? 'left',
                nl2br(e($settings['content'] ?? ''))
            ),
            'image' => sprintf(
                '<img src="%s" alt="%s" class="vc-image" style="max-width:100%%;height:%s">',
                $settings['url'] ?? '',
                e($settings['alt'] ?? ''),
                $settings['height'] ?? 'auto'
            ),
            default => '<!-- Unsupported block: ' . e($type) . ' -->',
        };
    }

    // Валидация блоков
    public function validateBlocks(array $sections): array
    {
        $errors = [];
        $sections = $this->normalizeSections($sections);

        foreach ($sections as $index => $section) {
            foreach ($section['blocks'] ?? [] as $blockIndex => $block) {
                $type = $block['type'] ?? null;
                
                if (!$type) {
                    $errors[] = "Block at section {$index}, position {$blockIndex} has no type";
                    continue;
                }

                $blockConfig = BlockRegistry::get($type);
                
                if (!$blockConfig) {
                    $errors[] = "Unknown block type: {$type}";
                    continue;
                }

                // Валидация полей блока
                $validation = $this->validateBlockFields($type, $block['settings'] ?? []);
                
                if ($validation !== true) {
                    $errors[] = "Block {$type} validation failed: {$validation}";
                }
            }
        }

        return $errors;
    }

    protected function validateBlockFields(string $type, array $settings): bool|string
    {
        $block = BlockRegistry::get($type);
        $fields = $block['fields'] ?? [];

        foreach ($fields as $field => $config) {
            $value = $settings[$field] ?? null;

            // Обязательные поля
            if (($config['required'] ?? false) && empty($value)) {
                return "Field {$field} is required";
            }

            // Типизированная валидация
            if (!$this->validateFieldType($field, $value, $config)) {
                return "Field {$field} has invalid value";
            }
        }

        return true;
    }

    protected function validateFieldType(string $field, $value, array $config): bool
    {
        if ($value === '') {
            return true;
        }

        return match ($config['type']) {
            'string' => is_string($value) || is_null($value),
            'number' => is_numeric($value) || is_null($value),
            'boolean' => is_bool($value) || is_null($value),
            'color' => preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $value) || is_null($value),
            'media' => is_numeric($value) || is_null($value),
            'select' => $this->validateSelectValue($value, $config['options'] ?? []),
            'array' => is_array($value) || is_null($value),
            default => true,
        };
    }

    protected function validateSelectValue(mixed $value, array $options): bool
    {
        if (is_null($value)) {
            return true;
        }

        $allowedValues = array_map('strval', array_keys($options));
        $allowedLabels = array_map('strval', array_values($options));

        return in_array((string) $value, $allowedValues, true)
            || in_array((string) $value, $allowedLabels, true);
    }

    // Экспорт/Импорт
    public function exportSections(array $sections): string
    {
        return json_encode([
            'version' => '2.0',
            'exported_at' => now()->toIso8601String(),
            'sections' => $sections,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function importSections(string $json): array
    {
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON: ' . json_last_error_msg());
        }

        if (($data['version'] ?? '1.0') !== '2.0') {
            throw new \Exception('Unsupported export version');
        }

        $sections = $this->normalizeSections($data['sections'] ?? []);
        $errors = $this->validateBlocks($sections);
        
        if (!empty($errors)) {
            throw new \Exception('Validation failed: ' . implode(', ', $errors));
        }

        return $sections;
    }

    // Поиск по блокам
    public function searchBlocks(array $sections, string $query): array
    {
        $results = [];
        $sections = $this->normalizeSections($sections);
        
        foreach ($sections as $sectionIndex => $section) {
            foreach ($section['blocks'] ?? [] as $blockIndex => $block) {
                $content = json_encode($block);
                
                if (stripos($content, $query) !== false) {
                    $results[] = [
                        'section' => $sectionIndex,
                        'block' => $blockIndex,
                        'type' => $block['type'],
                        'preview' => Str::limit($content, 100),
                    ];
                }
            }
        }

        return $results;
    }

    public function normalizeSections(array $sections): array
    {
        if ($sections === []) {
            return [];
        }

        $looksLikeFlatBlocks = collect($sections)->every(
            fn ($item) => is_array($item) && array_key_exists('type', $item)
        );

        if ($looksLikeFlatBlocks) {
            $sections = [[
                'id' => Str::uuid()->toString(),
                'settings' => [],
                'blocks' => $sections,
            ]];
        }

        return array_values(array_map(function ($section): array {
            $section = is_array($section) ? $section : [];
            $sectionSettings = is_array($section['settings'] ?? null) ? $section['settings'] : [];
            $blocks = is_array($section['blocks'] ?? null) ? $section['blocks'] : [];

            return [
                'id' => (string) ($section['id'] ?? Str::uuid()->toString()),
                'settings' => $sectionSettings,
                'blocks' => array_values(array_map(function ($block): array {
                    $block = is_array($block) ? $block : [];

                    return [
                        'id' => (string) ($block['id'] ?? Str::uuid()->toString()),
                        'type' => (string) ($block['type'] ?? 'unknown'),
                        'settings' => is_array($block['settings'] ?? null)
                            ? $block['settings']
                            : collect($block)->except(['id', 'type'])->all(),
                    ];
                }, $blocks)),
            ];
        }, $sections));
    }

    protected function normalizeContentSnapshot(Page $page, array $content): array
    {
        $layout = 'default';
        $version = '1.0';
        $sections = $content;

        if (array_key_exists('sections', $content)) {
            $sections = is_array($content['sections'] ?? null) ? $content['sections'] : [];
            $layout = (string) ($content['layout'] ?? ($page->content_json['layout'] ?? 'default'));
            $version = (string) ($content['version'] ?? ($page->content_json['version'] ?? '1.0'));
        } else {
            $layout = (string) ($page->content_json['layout'] ?? 'default');
            $version = (string) ($page->content_json['version'] ?? '1.0');
        }

        return [
            'version' => $version,
            'layout' => $layout,
            'sections' => $this->normalizeSections(is_array($sections) ? $sections : []),
        ];
    }
}
