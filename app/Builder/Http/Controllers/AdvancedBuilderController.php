<?php

namespace App\Builder\Http\Controllers;

use App\Builder\Config\BlockRegistry;
use App\Builder\Config\SectionRegistry;
use App\Builder\Services\PageBuilderService;
use App\Builder\Services\DesignSystemService;
use App\Builder\Support\BuilderContractSerializer;
use App\Builder\Support\BuilderLibraryManager;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageRevision;
use App\System\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdvancedBuilderController extends Controller
{
    public function __construct(
        private readonly PageBuilderService $builder,
        private readonly BuilderLibraryManager $library,
        private readonly BuilderContractSerializer $serializer,
        private readonly ActivityLogService $activityLog,
        private readonly DesignSystemService $designSystem,
    ) {}

    public function advanced(Request $request, Page $page): View
    {
        $page->load('seoMeta');

        return view('admin.builder.advanced', [
            'page' => $page,
            'config' => $this->getBuilderConfig($request),
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
        $errors = $this->builder->validateBlocks($sections);

        if ($errors !== []) {
            return response()->json([
                'ok' => false,
                'errors' => $errors,
            ], 422);
        }

        DB::transaction(function () use ($page, $request, $sections): void {
            $this->builder->createRevision($page, $sections, 'auto-save');

            $page->forceFill([
                'content_json' => [
                    'version' => $page->content_json['version'] ?? '1.0',
                    'layout' => $page->content_json['layout'] ?? 'default',
                    'sections' => $sections,
                ],
                'updated_by' => $request->user()->id,
            ])->save();
        });

        return response()->json([
            'ok' => true,
            'saved_at' => now()->toIso8601String(),
            'updated_at' => $page->fresh()->updated_at?->toIso8601String(),
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
            'templates' => $this->library->visibleTemplates(request()),
        ]);
    }

    public function applyTemplate(Request $request, Page $page): JsonResponse
    {
        $request->validate([
            'template_id' => ['required', 'string'],
            'merge' => ['boolean'],
        ]);

        $template = $this->library->findVisibleTemplate($request->input('template_id'), $request);

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

    public function getSharedPresets(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $this->library->visiblePresets($request),
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

        $presets = $this->library->allPresets();
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
        $this->library->storeSharedPresets($presets);
        $this->activityLog->record('builder.preset.create', 'settings', null, 'Builder shared preset created.', [
            'preset_id' => $preset['id'],
            'preset_type' => $preset['type'],
        ], $request);

        return response()->json([
            'ok' => true,
            'data' => $this->library->decorateLibraryItem($preset, $request),
            'presets' => $this->library->visiblePresets($request),
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

        $existing = collect($this->library->allPresets())->firstWhere('id', $presetId);
        abort_unless($existing, 404);
        abort_unless($this->library->canManageLibraryItem($existing, $request), 403);

        $presets = collect($this->library->allPresets())->map(function (array $preset) use ($presetId, $payload, $request) {
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

        $this->library->storeSharedPresets($presets);
        $this->activityLog->record('builder.preset.update', 'settings', null, 'Builder shared preset updated.', [
            'preset_id' => $presetId,
        ], $request);

        return response()->json([
            'ok' => true,
            'data' => $this->library->decorateLibraryItem($updated, $request),
            'presets' => $this->library->visiblePresets($request),
        ]);
    }

    public function destroySharedPreset(Request $request, string $presetId): JsonResponse
    {
        $existing = collect($this->library->allPresets())->firstWhere('id', $presetId);
        abort_unless($existing, 404);
        abort_unless($this->library->canManageLibraryItem($existing, $request), 403);

        $presets = collect($this->library->allPresets())
            ->reject(fn (array $preset) => ($preset['id'] ?? null) === $presetId)
            ->values()
            ->all();

        $this->library->storeSharedPresets($presets);
        $this->activityLog->record('builder.preset.delete', 'settings', null, 'Builder shared preset deleted.', [
            'preset_id' => $presetId,
        ], $request);

        return response()->json([
            'ok' => true,
            'presets' => $this->library->visiblePresets($request),
        ]);
    }

    public function getSharedTemplates(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $this->library->visibleTemplates($request),
        ]);
    }

    public function getDesignLibrary(Request $request): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $this->library->designLibraryWorkspace($request),
        ]);
    }

    public function designLibrary(Request $request): View
    {
        return view('admin.builder.design-library', [
            'workspace' => $this->library->designLibraryWorkspace($request),
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

        $templates = $this->library->allSharedTemplates();
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
        $this->library->storeSharedTemplates($templates);
        $this->activityLog->record('builder.template.create', 'settings', null, 'Builder shared template created.', [
            'template_id' => $template['id'],
        ], $request);

        return response()->json([
            'ok' => true,
            'data' => $this->library->decorateLibraryItem($template, $request),
            'templates' => $this->library->visibleTemplates($request),
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

        $existing = collect($this->library->allSharedTemplates())->firstWhere('id', $templateId);
        abort_unless($existing, 404);
        abort_unless($this->library->canManageLibraryItem($existing, $request), 403);

        $templates = collect($this->library->allSharedTemplates())->map(function (array $template) use ($templateId, $payload, $request) {
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
        $this->library->storeSharedTemplates($templates);
        $this->activityLog->record('builder.template.update', 'settings', null, 'Builder shared template updated.', [
            'template_id' => $templateId,
        ], $request);

        return response()->json([
            'ok' => true,
            'data' => $this->library->decorateLibraryItem($updated, $request),
            'templates' => $this->library->visibleTemplates($request),
        ]);
    }

    public function destroySharedTemplate(Request $request, string $templateId): JsonResponse
    {
        $existing = collect($this->library->allSharedTemplates())->firstWhere('id', $templateId);
        abort_unless($existing, 404);
        abort_unless($this->library->canManageLibraryItem($existing, $request), 403);

        $templates = collect($this->library->allSharedTemplates())
            ->reject(fn (array $template) => ($template['id'] ?? null) === $templateId)
            ->values()
            ->all();

        $this->library->storeSharedTemplates($templates);
        $this->activityLog->record('builder.template.delete', 'settings', null, 'Builder shared template deleted.', [
            'template_id' => $templateId,
        ], $request);

        return response()->json([
            'ok' => true,
            'templates' => $this->library->visibleTemplates($request),
        ]);
    }

    protected function calculateDiff(array $old, array $new): array
    {
        return [
            'sections_added' => count($new['sections'] ?? []) - count($old['sections'] ?? []),
            'blocks_changed' => $this->countBlocks($new) - $this->countBlocks($old),
        ];
    }

    protected function getBuilderConfig(?Request $request = null): array
    {
        $user = $request?->user();
        $serializedBlocks = $this->serializer->serializeRegistry(BlockRegistry::all());

        return [
            'responsive_preview' => true,
            'breakpoints' => [
                ['name' => 'desktop', 'label' => 'Desktop', 'width' => '100%', 'maxWidth' => '1200px'],
                ['name' => 'tablet', 'label' => 'Tablet', 'width' => '768px', 'maxWidth' => '768px'],
                ['name' => 'mobile', 'label' => 'Mobile', 'width' => '480px', 'maxWidth' => '480px'],
            ],
            'sections' => SectionRegistry::config(),
            'auto_save' => [
                'enabled' => true,
                'interval' => 120,
            ],
            'max_revisions' => 50,
            'categories' => BlockRegistry::getCategories(),
            'total_blocks' => count(BlockRegistry::all()),
            'blocks' => $serializedBlocks,
            'design_system' => $this->designSystem->tokens(),
            'quick_add' => [
                'templates' => $this->library->quickAddTemplates(),
            ],
            'design_library_url' => route('admin.pages.builder.design-library.index'),
            'media' => [
                'api_base' => url('/admin/api/media'),
                'folder_api_base' => url('/admin/api/media/folders'),
                'can_manage_folders' => $user?->hasPermission('media.edit') ?? false,
                'can_upload_media' => $user?->hasPermission('media.upload') ?? false,
                'can_edit_media' => $user?->hasPermission('media.edit') ?? false,
                'can_delete_media' => $user?->hasPermission('media.delete') ?? false,
            ],
        ];
    }

    protected function countBlocks(array $content): int
    {
        return collect($content['sections'] ?? [])
            ->sum(fn (array $section) => count($section['blocks'] ?? []));
    }
}
