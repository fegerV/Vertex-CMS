<?php

namespace App\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
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

        return response()
            ->view('frontend.sitemap', compact('pages'))
            ->header('Content-Type', 'application/xml');
    }
}
