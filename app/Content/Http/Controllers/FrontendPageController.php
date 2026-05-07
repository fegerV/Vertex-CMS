<?php

namespace App\Content\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class FrontendPageController extends Controller
{
    public function home(): View
    {
        $page = Page::query()
            ->where('uri', '/')
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->first();

        return view('frontend.page', compact('page'));
    }

    public function show(string $uri): View
    {
        $page = Page::query()
            ->where('uri', '/'.$uri)
            ->where('status', 'published')
            ->where(fn ($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->firstOrFail();

        return view('frontend.page', compact('page'));
    }
}
