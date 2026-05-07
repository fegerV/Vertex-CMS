<?php

namespace App\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = Page::query()->where('status', 'published')->get();

        return response()
            ->view('frontend.sitemap', compact('pages'))
            ->header('Content-Type', 'application/xml');
    }
}

