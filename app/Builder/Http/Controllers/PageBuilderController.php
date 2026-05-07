<?php

namespace App\Builder\Http\Controllers;

use App\Builder\Services\PageBuilderService;
use App\Builder\Services\PageRenderer;
use App\Content\Services\PageService;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PageBuilderController extends Controller
{
    public function __construct(
        private readonly PageService $pages,
        private readonly PageRenderer $renderer,
        private readonly PageBuilderService $builder,
    ) {
    }

    public function edit(Page $page): View
    {
        return view('admin.builder.edit', compact('page'));
    }

    public function update(Request $request, Page $page): JsonResponse
    {
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
        $payload = $request->validate([
            'content' => ['required', 'array'],
        ]);

        $sections = $payload['content'] ?? [];
        
        $html = $this->renderer->render([
            'version' => '1.0',
            'layout' => 'default',
            'sections' => $sections,
        ]);

        return response()->json([
            'html' => (string) $html,
        ]);
    }
}
