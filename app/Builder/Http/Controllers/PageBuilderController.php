<?php

namespace App\Builder\Http\Controllers;

use App\Builder\Services\PageBuilderService;
use App\Builder\Services\PageRenderer;
use App\Content\Services\PageService;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PageBuilderController extends Controller
{
    public function __construct(
        private readonly PageService $pages,
        private readonly PageRenderer $renderer,
        private readonly PageBuilderService $builder,
    ) {}

    public function edit(Request $request, Page $page): View
    {
        $this->authorizeBuilderAccess($request);

        return view('admin.builder.edit', compact('page'));
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $this->authorizeBuilderAccess($request);
        try {
            if ($request->has('content')) {
                $payload = $request->validate([
                    'content' => ['required', 'array'],
                    'title' => ['sometimes', 'string', 'max:255'],
                    'create_revision' => ['nullable', 'boolean'],
                ]);

                $sections = $this->builder->normalizeSections($payload['content']);
                $errors = $this->builder->validateBlocks($sections);

                if ($errors !== []) {
                    return response()->json(['ok' => false, 'errors' => $errors], 422);
                }

                if ($request->boolean('create_revision', true)) {
                    $this->builder->createRevision($page, $page->content_json['sections'] ?? [], 'manual-save');
                }

                if (isset($payload['title'])) {
                    $page->title = $payload['title'];
                }

                $content = [
                    'version' => '1.0',
                    'layout' => $page->content_json['layout'] ?? 'default',
                    'sections' => $sections,
                ];
            } else {
                $payload = $request->validate([
                    'content_json' => ['required', 'json'],
                ]);

                $content = json_decode($payload['content_json'], true);
                if (! is_array($content)) {
                    throw ValidationException::withMessages([
                        'content_json' => 'Invalid JSON content.',
                    ]);
                }

                $sections = $this->builder->normalizeSections($content['sections'] ?? []);
                $errors = $this->builder->validateBlocks($sections);
                if ($errors !== []) {
                    return response()->json(['ok' => false, 'errors' => $errors], 422);
                }
                $content = [
                    'version' => '1.0',
                    'layout' => is_string($content['layout'] ?? null) ? $content['layout'] : 'default',
                    'sections' => $sections,
                ];
            }

            $page->forceFill([
                'content_json' => $content,
                'updated_by' => $request->user()->id,
            ])->save();

            $page->load('seoMeta');

            return response()->json([
                'ok' => true,
                'message' => 'Page saved successfully.',
                'data' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'uri' => $page->uri,
                    'content_json' => $page->content_json,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function preview(Request $request, Page $page): JsonResponse
    {
        $this->authorizeBuilderAccess($request);
        $payload = $request->validate([
            'content' => ['required', 'array'],
            'document' => ['sometimes', 'boolean'],
        ]);

        $sections = $this->builder->normalizeSections($payload['content'] ?? []);
        $errors = $this->builder->validateBlocks($sections);

        if ($errors !== []) {
            Log::warning('Builder preview validation failed.', [
                'page_id' => $page->id,
                'document' => $request->boolean('document'),
                'errors' => $errors,
                'sections' => collect($sections)->map(fn (array $section): array => [
                    'id' => $section['id'] ?? null,
                    'blocks' => collect($section['blocks'] ?? [])->map(fn (array $block): array => [
                        'id' => $block['id'] ?? null,
                        'type' => $block['type'] ?? null,
                    ])->values()->all(),
                ])->values()->all(),
            ]);

            return response()->json([
                'ok' => false,
                'errors' => $errors,
                'error' => 'Preview validation failed.',
            ], 422);
        }

        try {
            $content = [
                'version' => '1.0',
                'layout' => 'default',
                'sections' => $sections,
            ];
            $wantsDocument = $request->boolean('document');
            $html = $wantsDocument ? '' : (string) $this->renderer->render($content);
            $document = $wantsDocument
                ? view('frontend.page', [
                    'page' => $page,
                    'html' => (string) $this->renderer->render($content, editor: true),
                    'builderPreview' => true,
                ])->render()
                : null;

            return response()->json([
                'ok' => true,
                'html' => $html,
                'document' => $document,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'error' => 'Preview rendering failed.',
                'message' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function authorizeBuilderAccess(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('pages.edit'), 403);
    }
}
