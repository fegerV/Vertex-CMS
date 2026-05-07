<?php

namespace App\Content\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class FrontendPageController extends Controller
{
    public function home(): View
    {
        $page = Page::query()->where('uri', '/')->first();

        return view('frontend.page', compact('page'));
    }

    public function show(string $uri): View
    {
        $page = Page::query()->where('uri', '/'.$uri)->firstOrFail();

        return view('frontend.page', compact('page'));
    }
}

