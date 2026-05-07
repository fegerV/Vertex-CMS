<?php

namespace App\Builder\Http\Controllers;

use App\Builder\Config\BlockRegistry;
use App\Builder\Services\PageBuilderService;
use App\Content\Services\PageService;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdvancedBuilderController extends Controller
{
    public function __construct(
        private readonly PageBuilderService $builder,
        private readonly PageService $pages,
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
            'templates' => [
                [
                    'id' => 'hero-banner',
                    'name' => 'Hero Banner',
                    'category' => 'landing',
                    'sections' => [
                        [
                            'settings' => ['background_color' => '#f8fafc', 'padding_top' => 48, 'padding_bottom' => 48],
                            'blocks' => [
                                [
                                    'type' => 'heading',
                                    'settings' => [
                                        'level' => 'h1',
                                        'text' => 'Page headline',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'settings' => [
                                        'content' => 'Describe the main value proposition in one short paragraph.',
                                        'align' => 'center',
                                    ],
                                ],
                                [
                                    'type' => 'button',
                                    'settings' => [
                                        'text' => 'Get started',
                                        'url' => '#',
                                        'style' => 'primary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'faq-section',
                    'name' => 'FAQ Section',
                    'category' => 'content',
                    'sections' => [
                        [
                            'settings' => ['padding_top' => 32, 'padding_bottom' => 32],
                            'blocks' => [
                                [
                                    'type' => 'heading',
                                    'settings' => ['level' => 'h2', 'text' => 'Frequently asked questions'],
                                ],
                                [
                                    'type' => 'faq',
                                    'settings' => [
                                        'items' => [
                                            ['question' => 'How does it work?', 'answer' => 'Add your answer here.'],
                                            ['question' => 'Can I customize it?', 'answer' => 'Yes, each block can be edited.'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function applyTemplate(Request $request, Page $page): JsonResponse
    {
        $request->validate([
            'template_id' => ['required', 'string'],
            'merge' => ['boolean'],
        ]);

        $template = collect($this->getTemplates()->getData(true)['templates'] ?? [])
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
}
