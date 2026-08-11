<?php

namespace App\Builder\Support;

use App\Core\Services\SettingsService;
use App\Models\Setting;
use Illuminate\Http\Request;

class BuilderLibraryManager
{
    public function __construct(private readonly SettingsService $settings)
    {
    }

    public function visiblePresets(Request $request): array
    {
        return collect($this->allPresets())
            ->filter(fn (array $preset) => $this->canViewLibraryItem($preset, $request))
            ->map(fn (array $preset) => $this->decorateLibraryItem($preset, $request))
            ->values()
            ->all();
    }

    public function allPresets(): array
    {
        $value = $this->settings->get('builder.shared_presets', []);

        return is_array($value) ? array_values($value) : [];
    }

    public function storeSharedPresets(array $presets): void
    {
        Setting::query()->updateOrCreate(
            ['group_name' => 'builder', 'setting_key' => 'shared_presets'],
            [
                'setting_value' => json_encode(array_values($presets), JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'autoload' => true,
            ],
        );

        $this->settings->forgetCache();
    }

    public function visibleTemplates(Request $request): array
    {
        return collect(array_merge($this->templateLibrary(), $this->allSharedTemplates()))
            ->filter(fn (array $template) => $this->canViewLibraryItem($template, $request))
            ->map(fn (array $template) => $this->decorateLibraryItem($template, $request))
            ->values()
            ->all();
    }

    public function findVisibleTemplate(string $templateId, Request $request): ?array
    {
        return collect($this->visibleTemplates($request))->firstWhere('id', $templateId);
    }

    public function designLibraryWorkspace(Request $request): array
    {
        $templates = $this->visibleTemplates($request);
        $starters = collect($this->quickAddTemplates())
            ->map(fn (array $starter) => $this->decorateStarterItem($starter))
            ->values()
            ->all();
        $presets = $this->visiblePresets($request);

        return [
            'version' => '1.0',
            'generated_at' => now()->toIso8601String(),
            'navigation' => [
                ['id' => 'templates', 'label' => 'Templates', 'count' => count($templates)],
                ['id' => 'starters', 'label' => 'Starters', 'count' => count($starters)],
                ['id' => 'presets', 'label' => 'Presets', 'count' => count($presets)],
            ],
            'stats' => [
                'templates' => count($templates),
                'starters' => count($starters),
                'presets' => count($presets),
                'builtin_templates' => collect($templates)->where('source', 'builtin')->count(),
                'shared_templates' => collect($templates)->where('source', 'shared')->count(),
                'editable_items' => collect(array_merge($templates, $presets))->where('can_edit', true)->count(),
            ],
            'categories' => [
                'templates' => $this->summarizeLibraryGroups($templates, 'category'),
                'starters' => $this->summarizeLibraryGroups($starters, 'category'),
                'presets' => $this->summarizeLibraryGroups($presets, 'type'),
            ],
            'collections' => [
                [
                    'id' => 'templates',
                    'label' => 'Page and section templates',
                    'description' => 'Reusable page sections with previews, ownership and visibility metadata.',
                    'items' => $templates,
                ],
                [
                    'id' => 'starters',
                    'label' => 'Quick-start compositions',
                    'description' => 'Small block stacks optimized for fast insertion from the builder canvas.',
                    'items' => $starters,
                ],
                [
                    'id' => 'presets',
                    'label' => 'Block presets',
                    'description' => 'Reusable settings snapshots for individual block types.',
                    'items' => $presets,
                ],
            ],
            'empty_states' => [
                'templates' => 'Save a section as a shared template to grow the design library.',
                'starters' => 'Starter compositions are shipped by Vertex and can be extended by modules.',
                'presets' => 'Select a block and save its settings as a reusable preset.',
            ],
        ];
    }

    public function allSharedTemplates(): array
    {
        $value = $this->settings->get('builder.shared_templates', []);

        return is_array($value) ? array_values($value) : [];
    }

    public function storeSharedTemplates(array $templates): void
    {
        Setting::query()->updateOrCreate(
            ['group_name' => 'builder', 'setting_key' => 'shared_templates'],
            [
                'setting_value' => json_encode(array_values($templates), JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'autoload' => true,
            ],
        );

        $this->settings->forgetCache();
    }

    public function canManageLibraryItem(array $item, Request $request): bool
    {
        if (($item['source'] ?? null) === 'builtin') {
            return false;
        }

        return (int) ($item['owner_id'] ?? 0) === (int) ($request->user()?->id ?? 0) || $this->isSuperAdmin($request);
    }

    public function decorateLibraryItem(array $item, Request $request): array
    {
        $isOwner = (int) ($item['owner_id'] ?? 0) === (int) ($request->user()?->id ?? 0);
        $isBuiltin = ($item['source'] ?? null) === 'builtin';
        $sectionsCount = count($item['sections'] ?? []);
        $blocksCount = collect($item['sections'] ?? [])
            ->sum(fn (array $section) => count($section['blocks'] ?? []));

        return [
            ...$item,
            'description' => $item['description'] ?? null,
            'source' => $item['source'] ?? 'shared',
            'visibility' => $item['visibility'] ?? 'shared',
            'thumbnail' => $item['thumbnail'] ?? $this->buildTemplateThumbnail($item),
            'sections_count' => $sectionsCount,
            'blocks_count' => $blocksCount,
            'can_edit' => ! $isBuiltin && ($isOwner || $this->isSuperAdmin($request)),
            'can_delete' => ! $isBuiltin && ($isOwner || $this->isSuperAdmin($request)),
            'owner' => $item['owner_name'] ?? null,
        ];
    }

    public function templateLibrary(): array
    {
        return [
            [
                'id' => 'hero-banner',
                'name' => 'Hero — основной экран',
                'category' => 'landing',
                'description' => 'Готовый полноэкранный hero с заголовком, подзаголовком, фоном и основной кнопкой.',
                'source' => 'builtin',
                'visibility' => 'shared',
                'sections' => [
                    [
                        'settings' => ['background_color' => '#f8fafc', 'padding_top' => 48, 'padding_bottom' => 48],
                        'blocks' => [
                            ['type' => 'hero', 'settings' => [
                                'title' => 'Создавайте страницы без ограничений',
                                'subtitle' => 'Соберите выразительный первый экран и настройте каждый элемент под свой бренд.',
                                'background' => '',
                                'title_color' => '#ffffff',
                                'subtitle_color' => '#e2e8f0',
                                'button_text' => 'Начать работу',
                                'button_url' => '#content',
                                'button_target' => '_self',
                                'button_bg_color' => '#0ea5e9',
                                'button_text_color' => '#ffffff',
                                'button_border_color' => 'transparent',
                                'padding_top' => 112,
                                'padding_bottom' => 112,
                            ]],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'landing-page-starter',
                'name' => 'Лендинг — стартовая страница',
                'category' => 'page',
                'description' => 'Заготовка целой страницы: hero, преимущества, призыв к действию и FAQ.',
                'source' => 'builtin',
                'visibility' => 'shared',
                'sections' => [
                    [
                        'settings' => ['background_color' => '#0f172a', 'padding_top' => 0, 'padding_bottom' => 0],
                        'blocks' => [[
                            'type' => 'hero',
                            'settings' => [
                                'title' => 'Понятный заголовок вашего продукта',
                                'subtitle' => 'Одним предложением объясните, кому и какую пользу вы приносите.',
                                'background' => '',
                                'title_color' => '#ffffff',
                                'subtitle_color' => '#cbd5e1',
                                'button_text' => 'Получить предложение',
                                'button_url' => '#contact',
                                'button_target' => '_self',
                                'button_bg_color' => '#14b8a6',
                                'button_text_color' => '#ffffff',
                                'button_border_color' => 'transparent',
                                'padding_top' => 104,
                                'padding_bottom' => 104,
                            ],
                        ]],
                    ],
                    [
                        'settings' => ['background_color' => '#ffffff', 'padding_top' => 64, 'padding_bottom' => 64],
                        'blocks' => [
                            ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Почему выбирают нас', 'align' => 'center', 'color' => '#0f172a']],
                            ['type' => 'text', 'settings' => ['content' => "Первое преимущество — конкретный результат.\nВторое преимущество — простой и прозрачный процесс.\nТретье преимущество — поддержка на каждом этапе.", 'align' => 'center', 'color' => '#475569']],
                        ],
                    ],
                    [
                        'settings' => ['background_color' => '#f1f5f9', 'padding_top' => 56, 'padding_bottom' => 56],
                        'blocks' => [
                            ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Готовы обсудить задачу?', 'align' => 'center', 'color' => '#0f172a']],
                            ['type' => 'text', 'settings' => ['content' => 'Оставьте заявку — мы предложим следующий шаг без лишних обязательств.', 'align' => 'center', 'color' => '#475569']],
                            ['type' => 'button', 'settings' => ['text' => 'Связаться с нами', 'url' => '#contact', 'style' => 'primary', 'target' => '_self']],
                        ],
                    ],
                    [
                        'settings' => ['background_color' => '#ffffff', 'padding_top' => 56, 'padding_bottom' => 56],
                        'blocks' => [
                            ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Частые вопросы', 'align' => 'left', 'color' => '#0f172a']],
                            ['type' => 'faq', 'settings' => ['items' => [
                                ['question' => 'С чего начать?', 'answer' => 'Расскажите о задаче и желаемом результате.'],
                                ['question' => 'Можно ли изменить шаблон?', 'answer' => 'Да. Все секции, блоки, тексты, цвета и отступы доступны в инспекторе.'],
                            ]]],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'feature-section',
                'name' => 'Преимущество с изображением',
                'category' => 'content',
                'description' => 'Компактная секция продукта с изображением, заголовком, описанием и кнопкой.',
                'source' => 'builtin',
                'visibility' => 'shared',
                'sections' => [[
                    'settings' => ['background_color' => '#ffffff', 'padding_top' => 56, 'padding_bottom' => 56],
                    'blocks' => [
                        ['type' => 'image', 'settings' => ['media_id' => null, 'url' => '', 'alt' => 'Изображение преимущества', 'width' => '100%', 'height' => 'auto', 'radius' => 'md', 'shadow' => 'sm']],
                        ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Покажите главное преимущество', 'align' => 'left', 'color' => '#0f172a']],
                        ['type' => 'text', 'settings' => ['content' => 'Объясните пользу простым языком и подкрепите её подходящим изображением.', 'align' => 'left', 'color' => '#475569']],
                        ['type' => 'button', 'settings' => ['text' => 'Узнать подробнее', 'url' => '#', 'style' => 'secondary', 'target' => '_self']],
                    ],
                ]],
            ],
            [
                'id' => 'cta-section',
                'name' => 'CTA — призыв к действию',
                'category' => 'conversion',
                'description' => 'Контрастная секция с коротким сообщением и целевой кнопкой.',
                'source' => 'builtin',
                'visibility' => 'shared',
                'sections' => [[
                    'settings' => ['background_color' => '#0f172a', 'padding_top' => 56, 'padding_bottom' => 56],
                    'blocks' => [
                        ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Сделайте следующий шаг', 'align' => 'center', 'color' => '#ffffff']],
                        ['type' => 'text', 'settings' => ['content' => 'Коротко напомните о ценности предложения перед основной кнопкой.', 'align' => 'center', 'color' => '#cbd5e1']],
                        ['type' => 'button', 'settings' => ['text' => 'Оставить заявку', 'url' => '#contact', 'style' => 'primary', 'target' => '_self']],
                    ],
                ]],
            ],
            [
                'id' => 'faq-section',
                'name' => 'FAQ — частые вопросы',
                'category' => 'content',
                'description' => 'Готовая секция с заголовком и редактируемым списком популярных вопросов.',
                'source' => 'builtin',
                'visibility' => 'shared',
                'sections' => [
                    [
                        'settings' => ['padding_top' => 32, 'padding_bottom' => 32],
                        'blocks' => [
                            ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Частые вопросы']],
                            ['type' => 'faq', 'settings' => ['items' => [
                                ['question' => 'Как это работает?', 'answer' => 'Добавьте сюда короткий и полезный ответ.'],
                                ['question' => 'Можно ли всё настроить?', 'answer' => 'Да, каждый элемент редактируется в панели настроек.'],
                            ]]],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function quickAddTemplates(): array
    {
        return [
            [
                'id' => 'template-hero-heading',
                'name' => 'Hero-блок',
                'meta' => 'Hero · заголовок, фон и кнопка',
                'description' => 'Полноценный первый экран с настройками контента, фона, кнопки и отступов.',
                'kind' => 'template',
                'blocks' => [
                    ['type' => 'hero', 'settings' => [
                        'title' => 'Сильный заголовок первого экрана',
                        'subtitle' => 'Коротко раскройте ценность предложения для посетителя.',
                        'background' => '',
                        'title_color' => '#ffffff',
                        'subtitle_color' => '#e2e8f0',
                        'button_text' => 'Основное действие',
                        'button_url' => '#',
                        'button_target' => '_self',
                        'button_bg_color' => '#0ea5e9',
                        'button_text_color' => '#ffffff',
                        'button_border_color' => 'transparent',
                        'padding_top' => 96,
                        'padding_bottom' => 96,
                    ]],
                ],
            ],
            [
                'id' => 'template-image-feature',
                'name' => 'Преимущество с изображением',
                'meta' => 'Изображение · заголовок · текст',
                'description' => 'Заготовка преимущества с визуальным акцентом и описанием.',
                'kind' => 'template',
                'blocks' => [
                    ['type' => 'image', 'settings' => ['media_id' => null, 'url' => '', 'alt' => '', 'width' => '100%', 'height' => 'auto', 'radius' => 'md', 'shadow' => 'sm']],
                    ['type' => 'heading', 'settings' => ['level' => 'h3', 'text' => 'Название преимущества', 'align' => 'left', 'color' => '#111827', 'font_size' => '1.5rem']],
                    ['type' => 'text', 'settings' => ['content' => 'Опишите пользу этого преимущества одним содержательным абзацем.', 'align' => 'left', 'color' => '#4b5563']],
                ],
            ],
            [
                'id' => 'template-faq-starter',
                'name' => 'Частые вопросы',
                'meta' => 'Заголовок · список FAQ',
                'description' => 'Заготовка для вопросов и ответов.',
                'kind' => 'template',
                'blocks' => [
                    ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Частые вопросы', 'align' => 'left', 'color' => '#111827', 'font_size' => '1.5rem']],
                    ['type' => 'faq', 'settings' => ['items' => [
                        ['question' => 'Первый вопрос', 'answer' => 'Полезный и понятный ответ.'],
                        ['question' => 'Второй вопрос', 'answer' => 'Ещё один короткий ответ.'],
                    ]]],
                ],
            ],
            [
                'id' => 'template-cta-starter',
                'name' => 'Призыв к действию',
                'meta' => 'Заголовок · текст · кнопка',
                'description' => 'Компактная конверсионная заготовка для завершения смыслового блока.',
                'kind' => 'template',
                'blocks' => [
                    ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Готовы сделать следующий шаг?', 'align' => 'center', 'color' => '#111827', 'font_size' => '1.75rem']],
                    ['type' => 'text', 'settings' => ['content' => 'Подскажите посетителю, что произойдёт после нажатия кнопки.', 'align' => 'center', 'color' => '#4b5563']],
                    ['type' => 'button', 'settings' => ['text' => 'Оставить заявку', 'url' => '#contact', 'style' => 'primary', 'size' => 'md', 'target' => '_self']],
                ],
            ],
        ];
    }

    private function decorateStarterItem(array $starter): array
    {
        $blocks = array_values((array) ($starter['blocks'] ?? []));

        return [
            ...$starter,
            'source' => $starter['source'] ?? 'builtin',
            'visibility' => $starter['visibility'] ?? 'shared',
            'category' => $starter['category'] ?? 'starter',
            'thumbnail' => $starter['thumbnail'] ?? $this->buildTemplateThumbnail([
                'name' => $starter['name'] ?? 'Starter',
                'category' => $starter['category'] ?? 'starter',
                'sections' => [
                    ['blocks' => $blocks],
                ],
            ]),
            'blocks_count' => count($blocks),
            'can_edit' => false,
            'can_delete' => false,
        ];
    }

    private function summarizeLibraryGroups(array $items, string $field): array
    {
        return collect($items)
            ->groupBy(fn (array $item) => (string) ($item[$field] ?? 'uncategorized'))
            ->map(fn ($group, string $id): array => [
                'id' => $id,
                'label' => str($id)->replace(['-', '_'], ' ')->title()->toString(),
                'count' => $group->count(),
            ])
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function canViewLibraryItem(array $item, Request $request): bool
    {
        if (($item['source'] ?? null) === 'builtin') {
            return true;
        }

        if (($item['visibility'] ?? 'shared') === 'shared') {
            return true;
        }

        return (int) ($item['owner_id'] ?? 0) === (int) ($request->user()?->id ?? 0) || $this->isSuperAdmin($request);
    }

    private function buildTemplateThumbnail(array $item): string
    {
        $name = e($item['name'] ?? 'Template');
        $category = e(ucfirst((string) ($item['category'] ?? $item['source'] ?? 'template')));
        $blocks = collect($item['sections'] ?? [])
            ->flatMap(fn (array $section) => $section['blocks'] ?? [])
            ->map(fn (array $block) => strtoupper(substr((string) ($block['type'] ?? '?'), 0, 1)))
            ->filter()
            ->take(4)
            ->values()
            ->all();

        if ($blocks === []) {
            $blocks = ['T', 'P'];
        }

        $chips = '';
        foreach ($blocks as $index => $label) {
            $x = 26 + ($index * 52);
            $chips .= '<g>'
                . '<rect x="'.$x.'" y="92" width="38" height="38" rx="12" fill="rgba(255,255,255,0.18)" stroke="rgba(255,255,255,0.28)"/>'
                . '<text x="'.($x + 19).'" y="116" text-anchor="middle" font-size="15" font-family="Inter, Arial, sans-serif" fill="#ffffff" font-weight="700">'.$label.'</text>'
                . '</g>';
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="640" height="360" viewBox="0 0 640 360" fill="none">
  <defs>
    <linearGradient id="bg" x1="24" y1="20" x2="620" y2="340" gradientUnits="userSpaceOnUse">
      <stop stop-color="#0F172A"/>
      <stop offset="0.5" stop-color="#0F766E"/>
      <stop offset="1" stop-color="#22C55E"/>
    </linearGradient>
  </defs>
  <rect width="640" height="360" rx="28" fill="url(#bg)"/>
  <rect x="22" y="22" width="596" height="316" rx="24" fill="rgba(255,255,255,0.06)" stroke="rgba(255,255,255,0.18)"/>
  <text x="34" y="56" font-size="13" font-family="Inter, Arial, sans-serif" fill="rgba(255,255,255,0.68)" letter-spacing="1.6">VERTEXCMS TEMPLATE</text>
  <text x="34" y="118" font-size="34" font-family="Inter, Arial, sans-serif" fill="#ffffff" font-weight="800">{$name}</text>
  <text x="34" y="152" font-size="18" font-family="Inter, Arial, sans-serif" fill="rgba(255,255,255,0.74)">{$category}</text>
  <rect x="34" y="190" width="210" height="14" rx="7" fill="rgba(255,255,255,0.22)"/>
  <rect x="34" y="218" width="286" height="12" rx="6" fill="rgba(255,255,255,0.14)"/>
  <rect x="34" y="244" width="250" height="12" rx="6" fill="rgba(255,255,255,0.14)"/>
  {$chips}
</svg>
SVG;

        return 'data:image/svg+xml;utf8,'.rawurlencode($svg);
    }

    private function isSuperAdmin(Request $request): bool
    {
        return (bool) $request->user()?->roles()->where('slug', 'super-admin')->exists();
    }
}
