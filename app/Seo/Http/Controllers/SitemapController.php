<?php

namespace App\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Term;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = Page::query()
            ->with('seoMeta')
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(function ($query): void {
                $query
                    ->whereDoesntHave('seoMeta')
                    ->orWhereHas('seoMeta', function ($query): void {
                        $query
                            ->where('include_in_sitemap', true)
                            ->where('robots', 'index, follow');
                    });
            })
            ->get();

        $terms = Term::query()
            ->with('taxonomy')
            ->whereHas('pages', function ($query): void {
                $query
                    ->where('status', 'published')
                    ->where(fn ($builder) => $builder->whereNull('published_at')->orWhere('published_at', '<=', now()));
            })
            ->get()
            ->filter(function (Term $term): bool {
                $seo = $term->seo_json ?? [];
                $robots = $seo['robots'] ?? 'index, follow';
                $includeInSitemap = $seo['include_in_sitemap'] ?? true;

                return $robots === 'index, follow' && (bool) $includeInSitemap;
            });

        $entries = collect()
            ->merge($pages->map(fn (Page $page) => [
                'loc' => url($page->uri),
                'lastmod' => $page->updated_at,
            ]))
            ->merge($terms->map(fn (Term $term) => [
                'loc' => route('frontend.term-archive', [$term->taxonomy?->slug, $term->slug]),
                'lastmod' => $term->updated_at,
            ]));

        return response()
            ->view('frontend.sitemap', ['entries' => $entries])
            ->header('Content-Type', 'application/xml');
    }
}
