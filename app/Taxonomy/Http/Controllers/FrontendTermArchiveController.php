<?php

namespace App\Taxonomy\Http\Controllers;

use App\Builder\Services\PageRenderer;
use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Theme\Services\ThemeManager;
use Illuminate\View\View;

class FrontendTermArchiveController extends Controller
{
    public function __construct(
        private readonly ThemeManager $themes,
        private readonly PageRenderer $renderer,
    ) {
    }

    public function show(string $taxonomy, string $term): View
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

        return view($this->themes->termArchiveView(), [
            'term' => $termModel,
            'taxonomy' => $termModel->taxonomy,
            'pages' => $termModel->pages,
            'renderer' => $this->renderer,
        ]);
    }
}
