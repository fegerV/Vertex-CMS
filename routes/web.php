<?php

use App\Seo\Http\Controllers\RobotsController;
use App\Seo\Http\Controllers\SitemapController;
use App\Content\Http\Controllers\FrontendPageController;
use App\System\Http\Controllers\PwaController;
use App\System\Http\Controllers\PwaManifestController;
use App\Taxonomy\Http\Controllers\FrontendTermArchiveController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => '{locale?}',
    'where' => ['locale' => 'ru|en'],
], function () {
    Route::get('/', [FrontendPageController::class, 'home'])->name('frontend.home');
    
    // Taxonomies
    Route::get('/taxonomy/{taxonomy}/{term}', [FrontendTermArchiveController::class, 'show'])->name('frontend.term-archive');

    // Catch-all page route
    Route::get('/{uri}', [FrontendPageController::class, 'show'])
        ->where('uri', '.*')
        ->name('frontend.page');
});

// Non-localized global routes
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('frontend.sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('frontend.robots');
Route::get('/manifest.webmanifest', [PwaManifestController::class, 'index'])->name('frontend.manifest');
Route::get('/service-worker.js', [PwaController::class, 'serviceWorker'])->name('frontend.service-worker');
Route::get('/offline', [PwaController::class, 'offline'])->name('frontend.offline');

// Public Form Endpoints (vertex-forms module) - must be BEFORE catch-all route
Route::prefix('forms')->name('public.forms.')->group(function () {
    require base_path('modules/vertex-forms/routes/web.php');
});
