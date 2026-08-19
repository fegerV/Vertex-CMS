<?php

namespace App\Content\Http\Controllers;

use App\Analytics\Services\TrafficAnalyticsService;
use App\Builder\Services\PageRenderer;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Theme\Services\ThemeManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontendPageController extends Controller
{
    public function __construct(
        private readonly TrafficAnalyticsService $analytics,
        private readonly PageRenderer $renderer,
        private readonly ThemeManager $themes,
    ) {
    }

    public function home(Request $request): View
    {
        $page = Page::query()
            ->with('seoMeta.ogImage')
            ->where('uri', '/')
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->first();

        if ($page) {
            $this->analytics->trackPage($page, $request);
        }

        return view($this->themes->pageView($page), [
            'page' => $page,
            'html' => $this->renderer->render($page?->content_json),
            'theme' => $this->themes->metadata(),
        ]);
    }

    public function show(Request $request, string $uri): View
    {
        $page = Page::query()
            ->with('seoMeta.ogImage')
            ->where('uri', '/'.$uri)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->firstOrFail();

        $this->analytics->trackPage($page, $request);

        return view($this->themes->pageView($page), [
            'page' => $page,
            'html' => $this->renderer->render($page->content_json),
            'theme' => $this->themes->metadata(),
        ]);
    }
}
