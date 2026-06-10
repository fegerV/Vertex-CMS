<?php

namespace App\Content\Http\Controllers;

use App\Builder\Services\PageRenderer;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class FrontendPageController extends Controller
{
    public function __construct(
        private readonly PageRenderer $renderer,
    ) {
    }

    public function home(?string $locale = null): View
    {
        $uri = $locale ? "/{$locale}" : "/";
        $page = Page::query()
            ->with('seoMeta.ogImage')
            ->where('uri', $uri)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->first();

        return view('frontend.page', [
            'page' => $page,
            'html' => $this->renderer->render($page?->content_json),
        ]);
    }

    public function show(?string $locale, string $uri): View
    {
        $fullUri = $locale ? "/{$locale}/{$uri}" : "/{$uri}";
        $page = Page::query()
            ->with('seoMeta.ogImage')
            ->where('uri', $fullUri)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->firstOrFail();

        return view('frontend.page', [
            'page' => $page,
            'html' => $this->renderer->render($page->content_json),
        ]);
    }
}
