<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Analytics\DashboardController;
use App\Http\Controllers\Analytics\HeatmapController;
use App\Http\Controllers\Api\AIController;

/*
|--------------------------------------------------------------------------
| API Routes - Analytics & AI
|--------------------------------------------------------------------------
*/

// Analytics Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Dashboards
    Route::apiResource('dashboards', DashboardController::class);
    Route::get('dashboards/{dashboard}/data', [DashboardController::class, 'getData']);
    
    // Heatmaps
    Route::get('heatmaps', [HeatmapController::class, 'index']);
    Route::post('heatmaps', [HeatmapController::class, 'store']);
    Route::get('heatmaps/{heatmap}', [HeatmapController::class, 'show']);
    Route::post('heatmaps/record', [HeatmapController::class, 'record']);
    
    // AI Services
    Route::post('ai/chat', [AIController::class, 'chat']);
    Route::post('ai/faq', [AIController::class, 'faq']);
    Route::post('ai/generate', [AIController::class, 'generateContent']);
    Route::post('ai/analyze-image', [AIController::class, 'analyzeImage']);
    Route::post('ai/moderate', [AIController::class, 'moderateContent']);
    Route::get('ai/search', [AIController::class, 'search']);
    Route::get('ai/suggest', [AIController::class, 'suggestQueries']);
    Route::post('ai/keywords', [AIController::class, 'extractKeywords']);
    Route::post('ai/language', [AIController::class, 'detectLanguage']);
    Route::post('ai/recommend', [AIController::class, 'recommendProducts']);
});
