<?php

namespace App\Taxonomy\Http\Controllers;

use App\Content\Http\Resources\PageResource;
use App\Http\Controllers\Controller;
use App\Models\Taxonomy;
use App\Models\Term;
use App\Support\Api\ApiResponse;
use App\Taxonomy\Http\Resources\TaxonomyResource;
use App\Taxonomy\Http\Resources\TermResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicTaxonomyApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(config_value('api.public_enabled', true), 403, 'Public API is disabled.');

        $taxonomies = Taxonomy::query()
            ->with('terms.taxonomy')
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            TaxonomyResource::collection($taxonomies)->resolve($request)
        );
    }

    public function termPages(Request $request, string $taxonomy, string $term): JsonResponse
    {
        abort_unless(config_value('api.public_enabled', true), 403, 'Public API is disabled.');

        $termModel = Term::query()
            ->with('taxonomy')
            ->where('slug', $term)
            ->whereHas('taxonomy', fn ($query) => $query->where('slug', $taxonomy))
            ->firstOrFail();

        $pages = $termModel->pages()
            ->with(['seoMeta', 'terms.taxonomy'])
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderBy('title')
            ->paginate(50);

        return ApiResponse::paginated(
            $pages,
            PageResource::collection($pages->getCollection())->resolve($request),
            [
                'term' => TermResource::make($termModel)->resolve($request),
            ]
        );
    }
}
