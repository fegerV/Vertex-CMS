<?php

namespace App\Content\Http\Controllers;

use App\Content\Services\PageService;
use App\Content\Http\Resources\PageResource;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class PageApiController extends Controller
{
    public function __construct(private readonly PageService $pages) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Page::query()->with('seoMeta');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if (!$request->has('include_deleted')) {
            $query->withoutTrashed();
        }

        $items = $query->latest()->paginate($request->get('per_page', 50));

        return PageResource::collection($items);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $page = $this->pages->create($this->validated($request), $request->user());
            return response()->json(new PageResource($page), 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json(new PageResource($page->load('seoMeta')));
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        try {
            $page = $this->pages->update($page, $this->validated($request), $request->user());
            return response()->json(new PageResource($page));
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function destroy(Request $request, Page $page): JsonResponse
    {
        $this->pages->delete($page, $request->user());
        return response()->json(['ok' => true, 'id' => $page->id]);
    }

    private function validated(Request $request): array
    {
        $payload = $request->validate([
            'parent_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'template' => ['nullable', 'string', 'max:255'],
            'content_json' => ['nullable', 'json'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_canonical_url' => ['nullable', 'url', 'max:500'],
            'seo_robots' => ['nullable', 'in:index, follow,noindex, follow,index, nofollow,noindex, nofollow'],
            'seo_og_title' => ['nullable', 'string', 'max:255'],
            'seo_og_description' => ['nullable', 'string', 'max:500'],
            'seo_og_image' => ['nullable', 'integer'],
            'seo_schema_json' => ['nullable', 'json'],
            'seo_include_in_sitemap' => ['nullable', 'boolean'],
        ]);

        $payload['seo_include_in_sitemap'] = $request->boolean('seo_include_in_sitemap', true);

        return $payload;
    }
}

