<?php

use App\Content\Http\Controllers\FrontendPageApiController;
use App\Seo\Http\Controllers\RobotsController;
use App\Seo\Http\Controllers\SitemapController;
use App\System\Http\Controllers\PublicSettingsApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/public')->group(function (): void {
    // Pages
    Route::get('/pages', [FrontendPageApiController::class, 'index']);
    Route::get('/pages/by-uri', [FrontendPageApiController::class, 'byUri']);
    Route::get('/pages/by-uri/{uri}', [FrontendPageApiController::class, 'byUri']);
    Route::get('/pages/{page}', [FrontendPageApiController::class, 'show']);
    
    // Menus
    Route::get('/menus/{location}', [\App\System\Http\Controllers\PublicSettingsApiController::class, 'menu']);
    
    // Settings
    Route::get('/settings/site', [PublicSettingsApiController::class, 'site']);
    
    // SEO
    Route::get('/sitemap.xml', [SitemapController::class, 'index']);
    Route::get('/robots.txt', [RobotsController::class, 'index']);
});


