<?php

use App\Seo\Http\Controllers\RobotsController;
use App\Seo\Http\Controllers\SitemapController;
use App\Content\Http\Controllers\FrontendPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendPageController::class, 'home'])->name('frontend.home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('frontend.sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('frontend.robots');
Route::get('/{uri}', [FrontendPageController::class, 'show'])
    ->where('uri', '.*')
    ->name('frontend.page');

