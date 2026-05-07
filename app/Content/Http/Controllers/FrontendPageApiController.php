<?php

namespace App\Content\Http\Controllers;

use App\Content\Http\Resources\PageResource;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FrontendPageApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $pages = Page::query()
            ->with('seoMeta')
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('uri')
            ->paginate(50);

        return PageResource::collection($pages);
    }

    public function show(Page $page): JsonResponse
    {
        abort_unless($page->isPublished(), 404, 'Page not found or not published.');

        return response()->json(new PageResource($page->load('seoMeta')));
    }

    public function byUri(string $uri): JsonResponse
    {
        $page = Page::query()
            ->with('seoMeta')
            ->where('uri', '/'.$uri)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->firstOrFail();

        return response()->json(new PageResource($page));
    }
}
