<?php

namespace App\Content\Http\Controllers;

use App\Content\Http\Resources\PageResource;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FrontendPageApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(config_value('api.public_enabled', true), 403, 'Public API is disabled.');

        $pages = Page::query()
            ->with('seoMeta')
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('uri')
            ->paginate(50);

        return ApiResponse::paginated(
            $pages,
            PageResource::collection($pages->getCollection())->resolve($request)
        );
    }

    public function show(Request $request, Page $page): JsonResponse
    {
        abort_unless(config_value('api.public_enabled', true), 403, 'Public API is disabled.');
        abort_unless($page->isPublished(), 404, 'Page not found or not published.');

        return ApiResponse::success(
            PageResource::make($page->load('seoMeta'))->resolve($request)
        );
    }

    public function byUri(Request $request, ?string $uri = null): JsonResponse
    {
        abort_unless(config_value('api.public_enabled', true), 403, 'Public API is disabled.');

        $normalizedUri = $uri ?? $request->string('uri')->toString();
        $normalizedUri = trim($normalizedUri);
        $normalizedUri = $normalizedUri === '' ? '/' : '/'.ltrim($normalizedUri, '/');

        $page = Page::query()
            ->with('seoMeta')
            ->where('uri', $normalizedUri)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->firstOrFail();

        return ApiResponse::success(
            PageResource::make($page)->resolve($request)
        );
    }
}
