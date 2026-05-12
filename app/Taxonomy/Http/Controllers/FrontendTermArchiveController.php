<?php

namespace App\Taxonomy\Http\Controllers;

use App\Analytics\Services\TrafficAnalyticsService;
use App\Builder\Services\PageRenderer;
use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Theme\Services\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontendTermArchiveController extends Controller
{
    public function __construct(
        private readonly TrafficAnalyticsService $analytics,
        private readonly ThemeManager $themes,
        private readonly PageRenderer $renderer,
    ) {
    }

    public function show(Request $request, string $taxonomy, string $term): View
    {
        $termModel = Term::query()
            ->with([
                'taxonomy',
                'pages' => fn ($query) => $query
                    ->with('seoMeta.ogImage')
                    ->where('status', 'published')
                    ->where(fn ($builder) => $builder->whereNull('published_at')->orWhere('published_at', '<=', now()))
                    ->orderBy('title'),
            ])
            ->whereHas('taxonomy', fn ($query) => $query->where('slug', $taxonomy))
            ->where('slug', $term)
            ->firstOrFail();

        $this->analytics->trackTermArchive($termModel, $request);

        $seo = $termModel->seo_json ?? [];
        $taxonomySettings = $termModel->taxonomy?->settings_json ?? [];
        $canonical = $seo['canonical_url'] ?? route('frontend.term-archive', [$termModel->taxonomy?->slug, $termModel->slug]);
        $title = $seo['title'] ?? $taxonomySettings['archive_title'] ?? "{$termModel->name} | ".config_value('site.name', 'VertexCMS');
        $description = $seo['description'] ?? $termModel->description ?? $taxonomySettings['archive_description'] ?? config_value('seo.default_description', '');
        $robots = $seo['robots'] ?? 'index, follow';

        return view($this->themes->termArchiveView(), [
            'term' => $termModel,
            'taxonomy' => $termModel->taxonomy,
            'pages' => $termModel->pages,
            'renderer' => $this->renderer,
            'meta' => [
                'title' => $title,
                'description' => $description,
                'canonical' => $canonical,
                'robots' => $robots,
            ],
        ]);
    }
}
