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
                'name' => 'Hero Banner',
                'category' => 'landing',
                'description' => 'A landing-style hero with headline, supporting copy and primary call to action.',
                'source' => 'builtin',
                'visibility' => 'shared',
                'sections' => [
                    [
                        'settings' => ['background_color' => '#f8fafc', 'padding_top' => 48, 'padding_bottom' => 48],
                        'blocks' => [
                            ['type' => 'heading', 'settings' => ['level' => 'h1', 'text' => 'Page headline', 'align' => 'center']],
                            ['type' => 'text', 'settings' => ['content' => 'Describe the main value proposition in one short paragraph.', 'align' => 'center']],
                            ['type' => 'button', 'settings' => ['text' => 'Get started', 'url' => '#', 'style' => 'primary']],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'faq-section',
                'name' => 'FAQ Section',
                'category' => 'content',
                'description' => 'A simple content section with heading and frequently asked questions list.',
                'source' => 'builtin',
                'visibility' => 'shared',
                'sections' => [
                    [
                        'settings' => ['padding_top' => 32, 'padding_bottom' => 32],
                        'blocks' => [
                            ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Frequently asked questions']],
                            ['type' => 'faq', 'settings' => ['items' => [
                                ['question' => 'How does it work?', 'answer' => 'Add your answer here.'],
                                ['question' => 'Can I customize it?', 'answer' => 'Yes, each block can be edited.'],
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
                'name' => 'Hero heading',
                'meta' => 'Template - heading + text + button',
                'description' => 'Hero-style section starter with a heading, supporting copy and CTA.',
                'kind' => 'template',
                'blocks' => [
                    ['type' => 'heading', 'settings' => ['level' => 'h1', 'text' => 'Launch a stronger headline', 'align' => 'left', 'color' => '#111827', 'font_size' => '2rem']],
                    ['type' => 'text', 'settings' => ['content' => 'Add a concise supporting paragraph for the section intro.', 'align' => 'left', 'color' => '#4b5563']],
                    ['type' => 'button', 'settings' => ['text' => 'Primary action', 'url' => '#', 'style' => 'primary', 'size' => 'md', 'target' => '_self']],
                ],
            ],
            [
                'id' => 'template-image-feature',
                'name' => 'Feature with image',
                'meta' => 'Template - image + heading + text',
                'description' => 'Feature section with a visual asset and supporting copy.',
                'kind' => 'template',
                'blocks' => [
                    ['type' => 'image', 'settings' => ['media_id' => null, 'url' => '', 'alt' => '', 'width' => '100%', 'height' => 'auto', 'radius' => 'md', 'shadow' => 'sm']],
                    ['type' => 'heading', 'settings' => ['level' => 'h3', 'text' => 'Feature title', 'align' => 'left', 'color' => '#111827', 'font_size' => '1.5rem']],
                    ['type' => 'text', 'settings' => ['content' => 'Describe this feature in one useful paragraph.', 'align' => 'left', 'color' => '#4b5563']],
                ],
            ],
            [
                'id' => 'template-faq-starter',
                'name' => 'FAQ starter',
                'meta' => 'Template - heading + faq',
                'description' => 'Starter stack for frequently asked questions.',
                'kind' => 'template',
                'blocks' => [
                    ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Frequently asked questions', 'align' => 'left', 'color' => '#111827', 'font_size' => '1.5rem']],
                    ['type' => 'faq', 'settings' => ['items' => [
                        ['question' => 'Question one', 'answer' => 'Answer one'],
                        ['question' => 'Question two', 'answer' => 'Answer two'],
                    ]]],
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
