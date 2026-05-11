<?php

namespace App\Builder\Http\Controllers;

use App\Builder\Config\BlockRegistry;
use App\Builder\Services\PageBuilderService;
use App\Content\Services\PageService;
use App\Core\Services\SettingsService;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Setting;
use App\System\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdvancedBuilderController extends Controller
{
    public function __construct(
        private readonly PageBuilderService $builder,
        private readonly PageService $pages,
        private readonly SettingsService $settings,
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function advanced(Page $page): View
    {
        $page->load('seoMeta');

        return view('admin.builder.advanced', [
            'page' => $page,
            'config' => $this->getBuilderConfig(),
        ]);
    }

    public function saveAdvanced(Request $request, Page $page): JsonResponse
    {
        $payload = $request->validate([
            'content' => ['required', 'array'],
            'title' => ['sometimes', 'string', 'max:255'],
            'create_revision' => ['boolean'],
        ]);

        $sections = $this->builder->normalizeSections($payload['content']);
        $errors = $this->builder->validateBlocks($sections);

        if ($errors !== []) {
            return response()->json([
                'ok' => false,
                'errors' => $errors,
            ], 422);
        }

        DB::beginTransaction();

        try {
            if ($payload['create_revision'] ?? true) {
                $this->builder->createRevision($page, $page->content_json['sections'] ?? [], 'manual-save');
            }

            if (isset($payload['title'])) {
                $page->title = $payload['title'];
            }

            $page->content_json = [
                'version' => '1.0',
                'layout' => $page->content_json['layout'] ?? 'default',
                'sections' => $sections,
            ];
            $page->updated_by = $request->user()->id;
            $page->save();

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Saved successfully',
                'page' => $page->fresh(),
                'revisions_count' => $page->revisions()->count(),
                'auto_save_at' => now()->addMinutes(2)->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function autoSave(Request $request, Page $page): JsonResponse
    {
        $payload = $request->validate([
            'content' => ['required', 'array'],
        ]);

        $sections = $this->builder->normalizeSections($payload['content']);
        $this->builder->createRevision($page, $sections, 'auto-save');

        return response()->json([
            'ok' => true,
            'saved_at' => now()->toIso8601String(),
        ]);
    }

    public function restoreRevision(Page $page, PageRevision $revision): JsonResponse
    {
        abort_unless($revision->page_id === $page->id, 404);

        $restored = $this->builder->restoreRevision($page, $revision);

        return response()->json([
            'ok' => true,
            'page' => $restored,
            'revision' => $revision,
        ]);
    }

    public function compareRevisions(Page $page, PageRevision $revisionA, PageRevision $revisionB): JsonResponse
    {
        abort_unless(
            $revisionA->page_id === $page->id
            && $revisionB->page_id === $page->id,
            404
        );

        return response()->json([
            'ok' => true,
            'comparison' => [
                'older' => [
                    'id' => $revisionA->id,
                    'title' => $revisionA->title,
                    'created_at' => $revisionA->created_at,
                    'blocks_count' => $this->countBlocks($revisionA->content_json),
                ],
                'newer' => [
                    'id' => $revisionB->id,
                    'title' => $revisionB->title,
                    'created_at' => $revisionB->created_at,
                    'blocks_count' => $this->countBlocks($revisionB->content_json),
                ],
                'diff' => $this->calculateDiff($revisionA->content_json, $revisionB->content_json),
            ],
        ]);
    }

    public function getRevisions(Page $page): JsonResponse
    {
        return response()->json($this->builder->getRevisions($page));
    }

    public function exportSections(Request $request): JsonResponse
    {
        $sections = $this->builder->normalizeSections($request->input('sections', []));

        return response()->json([
            'ok' => true,
            'export' => $this->builder->exportSections($sections),
            'filename' => 'vertex-sections-'.now()->format('Y-m-d').'.json',
        ]);
    }

    public function importSections(Request $request): JsonResponse
    {
        $request->validate([
            'import_data' => ['required', 'string'],
            'page_id' => ['sometimes', 'exists:pages,id'],
        ]);

        try {
            $sections = $this->builder->importSections($request->input('import_data'));

            if ($request->filled('page_id')) {
                $page = Page::query()->findOrFail($request->integer('page_id'));
                $page->content_json = [
                    'version' => '1.0',
                    'layout' => $page->content_json['layout'] ?? 'default',
                    'sections' => $sections,
                ];
                $page->save();
            }

            return response()->json([
                'ok' => true,
                'sections' => $sections,
                'imported_count' => count($sections),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function getTemplates(): JsonResponse
    {
        return response()->json([
            'templates' => $this->visibleTemplates(request()),
        ]);
    }

    public function applyTemplate(Request $request, Page $page): JsonResponse
    {
        $request->validate([
            'template_id' => ['required', 'string'],
            'merge' => ['boolean'],
        ]);

        $template = collect($this->visibleTemplates($request))
            ->firstWhere('id', $request->input('template_id'));

        abort_unless($template, 404, 'Template not found');

        $currentSections = $page->content_json['sections'] ?? [];
        $templateSections = $this->builder->normalizeSections($template['sections'] ?? []);
        $newSections = $request->boolean('merge')
            ? array_merge($currentSections, $templateSections)
            : $templateSections;

        $page->content_json = [
            'version' => '1.0',
            'layout' => $page->content_json['layout'] ?? 'default',
            'sections' => $newSections,
        ];
        $page->save();

        return response()->json([
            'ok' => true,
            'page' => $page->fresh(),
            'applied_template' => $template['name'],
        ]);
    }

    public function searchContent(Request $request, Page $page): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2'],
        ]);

        $results = $this->builder->searchBlocks(
            $page->content_json['sections'] ?? [],
            $request->input('q')
        );

        return response()->json([
            'ok' => true,
            'query' => $request->input('q'),
            'results' => $results,
            'count' => count($results),
        ]);
    }

    public function getSharedPresets(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $this->visiblePresets($request),
        ]);
    }

    public function storeSharedPreset(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', 'string', 'max:80'],
            'settings' => ['required', 'array'],
            'visibility' => ['nullable', 'in:private,shared'],
        ]);

        $presets = $this->allPresets();
        $preset = [
            'id' => (string) Str::uuid(),
            'name' => $payload['name'],
            'type' => $payload['type'],
            'settings' => $payload['settings'],
            'visibility' => $payload['visibility'] ?? 'shared',
            'owner_id' => $request->user()?->id,
            'owner_name' => $request->user()?->name,
            'updated_at' => now()->toIso8601String(),
            'created_by' => $request->user()?->name,
        ];

        array_unshift($presets, $preset);
        $this->storeSharedPresets($presets);
        $this->activityLog->record('builder.preset.create', 'settings', null, 'Builder shared preset created.', [
            'preset_id' => $preset['id'],
            'preset_type' => $preset['type'],
        ], $request);

        return response()->json([
            'ok' => true,
            'data' => $this->decorateLibraryItem($preset, $request),
            'presets' => $this->visiblePresets($request),
        ], 201);
    }

    public function updateSharedPreset(Request $request, string $presetId): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'type' => ['sometimes', 'string', 'max:80'],
            'settings' => ['sometimes', 'array'],
            'visibility' => ['sometimes', 'in:private,shared'],
        ]);

        $existing = collect($this->allPresets())->firstWhere('id', $presetId);
        abort_unless($existing, 404);
        abort_unless($this->canManageLibraryItem($existing, $request), 403);

        $presets = collect($this->allPresets())->map(function (array $preset) use ($presetId, $payload, $request) {
            if (($preset['id'] ?? null) !== $presetId) {
                return $preset;
            }

            return [
                ...$preset,
                ...$payload,
                'updated_at' => now()->toIso8601String(),
                'updated_by' => $request->user()?->name,
            ];
        })->values()->all();

        $updated = collect($presets)->firstWhere('id', $presetId);
        abort_unless($updated, 404);

        $this->storeSharedPresets($presets);
        $this->activityLog->record('builder.preset.update', 'settings', null, 'Builder shared preset updated.', [
            'preset_id' => $presetId,
        ], $request);

        return response()->json([
            'ok' => true,
            'data' => $this->decorateLibraryItem($updated, $request),
            'presets' => $this->visiblePresets($request),
        ]);
    }

    public function destroySharedPreset(Request $request, string $presetId): JsonResponse
    {
        $existing = collect($this->allPresets())->firstWhere('id', $presetId);
        abort_unless($existing, 404);
        abort_unless($this->canManageLibraryItem($existing, $request), 403);

        $presets = collect($this->allPresets())
            ->reject(fn (array $preset) => ($preset['id'] ?? null) === $presetId)
            ->values()
            ->all();

        $this->storeSharedPresets($presets);
        $this->activityLog->record('builder.preset.delete', 'settings', null, 'Builder shared preset deleted.', [
            'preset_id' => $presetId,
        ], $request);

        return response()->json([
            'ok' => true,
            'presets' => $this->visiblePresets($request),
        ]);
    }

    public function getSharedTemplates(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $this->visibleTemplates($request),
        ]);
    }

    public function storeSharedTemplate(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'sections' => ['required', 'array'],
            'visibility' => ['nullable', 'in:private,shared'],
        ]);

        $templates = $this->allSharedTemplates();
        $template = [
            'id' => (string) Str::uuid(),
            'name' => $payload['name'],
            'category' => $payload['category'] ?? 'custom',
            'sections' => $this->builder->normalizeSections($payload['sections']),
            'visibility' => $payload['visibility'] ?? 'shared',
            'owner_id' => $request->user()?->id,
            'owner_name' => $request->user()?->name,
            'updated_at' => now()->toIso8601String(),
            'source' => 'shared',
        ];

        array_unshift($templates, $template);
        $this->storeSharedTemplates($templates);
        $this->activityLog->record('builder.template.create', 'settings', null, 'Builder shared template created.', [
            'template_id' => $template['id'],
        ], $request);

        return response()->json([
            'ok' => true,
            'data' => $this->decorateLibraryItem($template, $request),
            'templates' => $this->visibleTemplates($request),
        ], 201);
    }

    public function updateSharedTemplate(Request $request, string $templateId): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'category' => ['sometimes', 'string', 'max:80'],
            'sections' => ['sometimes', 'array'],
            'visibility' => ['sometimes', 'in:private,shared'],
        ]);

        $existing = collect($this->allSharedTemplates())->firstWhere('id', $templateId);
        abort_unless($existing, 404);
        abort_unless($this->canManageLibraryItem($existing, $request), 403);

        $templates = collect($this->allSharedTemplates())->map(function (array $template) use ($templateId, $payload, $request) {
            if (($template['id'] ?? null) !== $templateId) {
                return $template;
            }

            if (isset($payload['sections'])) {
                $payload['sections'] = $this->builder->normalizeSections($payload['sections']);
            }

            return [
                ...$template,
                ...$payload,
                'updated_at' => now()->toIso8601String(),
                'updated_by' => $request->user()?->name,
            ];
        })->values()->all();

        $updated = collect($templates)->firstWhere('id', $templateId);
        $this->storeSharedTemplates($templates);
        $this->activityLog->record('builder.template.update', 'settings', null, 'Builder shared template updated.', [
            'template_id' => $templateId,
        ], $request);

        return response()->json([
            'ok' => true,
            'data' => $this->decorateLibraryItem($updated, $request),
            'templates' => $this->visibleTemplates($request),
        ]);
    }

    public function destroySharedTemplate(Request $request, string $templateId): JsonResponse
    {
        $existing = collect($this->allSharedTemplates())->firstWhere('id', $templateId);
        abort_unless($existing, 404);
        abort_unless($this->canManageLibraryItem($existing, $request), 403);

        $templates = collect($this->allSharedTemplates())
            ->reject(fn (array $template) => ($template['id'] ?? null) === $templateId)
            ->values()
            ->all();

        $this->storeSharedTemplates($templates);
        $this->activityLog->record('builder.template.delete', 'settings', null, 'Builder shared template deleted.', [
            'template_id' => $templateId,
        ], $request);

        return response()->json([
            'ok' => true,
            'templates' => $this->visibleTemplates($request),
        ]);
    }

    protected function calculateDiff(array $old, array $new): array
    {
        return [
            'sections_added' => count($new['sections'] ?? []) - count($old['sections'] ?? []),
            'blocks_changed' => $this->countBlocks($new) - $this->countBlocks($old),
        ];
    }

    protected function getBuilderConfig(): array
    {
        return [
            'responsive_preview' => true,
            'breakpoints' => [
                ['name' => 'desktop', 'label' => 'Desktop', 'width' => '100%', 'maxWidth' => '1200px'],
                ['name' => 'tablet', 'label' => 'Tablet', 'width' => '768px', 'maxWidth' => '768px'],
                ['name' => 'mobile', 'label' => 'Mobile', 'width' => '480px', 'maxWidth' => '480px'],
            ],
            'auto_save' => [
                'enabled' => true,
                'interval' => 120,
            ],
            'max_revisions' => 50,
            'categories' => BlockRegistry::getCategories(),
            'total_blocks' => count(BlockRegistry::all()),
        ];
    }

    protected function countBlocks(array $content): int
    {
        return collect($content['sections'] ?? [])
            ->sum(fn (array $section) => count($section['blocks'] ?? []));
    }

    protected function visiblePresets(Request $request): array
    {
        return collect($this->allPresets())
            ->filter(fn (array $preset) => $this->canViewLibraryItem($preset, $request))
            ->map(fn (array $preset) => $this->decorateLibraryItem($preset, $request))
            ->values()
            ->all();
    }

    protected function allPresets(): array
    {
        $value = $this->settings->get('builder.shared_presets', []);

        return is_array($value) ? array_values($value) : [];
    }

    protected function storeSharedPresets(array $presets): void
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

    protected function visibleTemplates(Request $request): array
    {
        return collect(array_merge($this->templateLibrary(), $this->allSharedTemplates()))
            ->filter(fn (array $template) => $this->canViewLibraryItem($template, $request))
            ->map(fn (array $template) => $this->decorateLibraryItem($template, $request))
            ->values()
            ->all();
    }

    protected function allSharedTemplates(): array
    {
        $value = $this->settings->get('builder.shared_templates', []);

        return is_array($value) ? array_values($value) : [];
    }

    protected function storeSharedTemplates(array $templates): void
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

    protected function templateLibrary(): array
    {
        return [
            [
                'id' => 'hero-banner',
                'name' => 'Hero Banner',
                'category' => 'landing',
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

    protected function canViewLibraryItem(array $item, Request $request): bool
    {
        if (($item['source'] ?? null) === 'builtin') {
            return true;
        }

        if (($item['visibility'] ?? 'shared') === 'shared') {
            return true;
        }

        return (int) ($item['owner_id'] ?? 0) === (int) ($request->user()?->id ?? 0) || $this->isSuperAdmin($request);
    }

    protected function canManageLibraryItem(array $item, Request $request): bool
    {
        if (($item['source'] ?? null) === 'builtin') {
            return false;
        }

        return (int) ($item['owner_id'] ?? 0) === (int) ($request->user()?->id ?? 0) || $this->isSuperAdmin($request);
    }

    protected function decorateLibraryItem(array $item, Request $request): array
    {
        $isOwner = (int) ($item['owner_id'] ?? 0) === (int) ($request->user()?->id ?? 0);
        $isBuiltin = ($item['source'] ?? null) === 'builtin';

        return [
            ...$item,
            'source' => $item['source'] ?? 'shared',
            'visibility' => $item['visibility'] ?? 'shared',
            'can_edit' => ! $isBuiltin && ($isOwner || $this->isSuperAdmin($request)),
            'can_delete' => ! $isBuiltin && ($isOwner || $this->isSuperAdmin($request)),
            'owner' => $item['owner_name'] ?? null,
        ];
    }

    protected function isSuperAdmin(Request $request): bool
    {
        return (bool) $request->user()?->roles()->where('slug', 'super-admin')->exists();
    }
}
