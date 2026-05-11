<?php

use App\Auth\Http\Controllers\ApiAuthController;
use App\Content\Http\Controllers\FrontendPageApiController;
use App\Seo\Http\Controllers\RobotsController;
use App\Seo\Http\Controllers\SitemapController;
use App\System\Http\Controllers\PublicSettingsApiController;
use App\Taxonomy\Http\Controllers\PublicTaxonomyApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/public')->middleware('throttle:api-public')->group(function (): void {
    // Pages
    Route::get('/pages', [FrontendPageApiController::class, 'index']);
    Route::get('/pages/by-uri', [FrontendPageApiController::class, 'byUri']);
    Route::get('/pages/by-uri/{uri}', [FrontendPageApiController::class, 'byUri']);
    Route::get('/pages/{page}', [FrontendPageApiController::class, 'show']);
    
    // Menus
    Route::get('/menus/{location}', [\App\System\Http\Controllers\PublicSettingsApiController::class, 'menu']);
    
    // Settings
    Route::get('/settings/site', [PublicSettingsApiController::class, 'site']);

    // Taxonomy
    Route::get('/taxonomies', [PublicTaxonomyApiController::class, 'index']);
    Route::get('/taxonomies/{taxonomy}/terms/{term}/pages', [PublicTaxonomyApiController::class, 'termPages']);
    
    // SEO
    Route::get('/sitemap.xml', [SitemapController::class, 'index']);
    Route::get('/robots.txt', [RobotsController::class, 'index']);
});

Route::prefix('api/v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:api-login');
        Route::middleware(['auth:sanctum', 'throttle:api-authenticated'])->group(function (): void {
            Route::get('/tokens', [ApiAuthController::class, 'tokens']);
            Route::post('/logout', [ApiAuthController::class, 'logout']);
            Route::delete('/tokens/{tokenId}', [ApiAuthController::class, 'destroyToken']);
        });
    });

    Route::middleware(['auth:sanctum', 'throttle:api-authenticated'])->group(function (): void {
        Route::get('/me', [ApiAuthController::class, 'me']);
    });
});


