<?php

use App\AI\Http\Controllers\AiController;
use App\Builder\Http\Controllers\BuilderApiController;
use App\Content\Http\Controllers\PageApiController;
use App\Media\Http\Controllers\MediaApiController;
use App\Media\Http\Controllers\MediaFolderApiController;
use App\System\Http\Controllers\SystemApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Analytics\DashboardController;
use App\Http\Controllers\Analytics\HeatmapController;
use App\Http\Controllers\Api\AIController;

Route::get('/pages', [PageApiController::class, 'index']);
Route::post('/pages', [PageApiController::class, 'store']);
Route::get('/pages/{page}', [PageApiController::class, 'show']);
Route::put('/pages/{page}', [PageApiController::class, 'update']);
Route::delete('/pages/{page}', [PageApiController::class, 'destroy']);

 // Media & Folders (admin panel uses session auth)
 Route::middleware(['web', 'auth'])->group(function (): void {
     Route::get('/media', [MediaApiController::class, 'index']);
     Route::post('/media/upload', [MediaApiController::class, 'store']);
     Route::put('/media/{media}', [MediaApiController::class, 'update']);
     Route::patch('/media/{media}/move', [MediaApiController::class, 'move']);
     Route::delete('/media/{media}', [MediaApiController::class, 'destroy']);

     // Media Folders
     Route::get('/media/folders', [MediaFolderApiController::class, 'index']);
     Route::post('/media/folders', [MediaFolderApiController::class, 'store']);
     Route::put('/media/folders/{folder}', [MediaFolderApiController::class, 'update']);
     Route::delete('/media/folders/{folder}', [MediaFolderApiController::class, 'destroy']);
 });

Route::get('/builder/blocks', [BuilderApiController::class, 'blocks']);
Route::post('/builder/render-preview', [BuilderApiController::class, 'renderPreview']);
// AI Providers & Chat (existing)
Route::get('/ai/providers', [AiController::class, 'providers']);
Route::post('/ai/chat', [AiController::class, 'chat']);

// Site Wizard - AI-powered site creation
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/ai/wizard/generate-structure', [AiController::class, 'wizardGenerateStructure']);
    Route::post('/ai/wizard/generate-semantic-core', [AiController::class, 'wizardGenerateSemanticCore']);
    Route::post('/ai/wizard/generate-article-plan', [AiController::class, 'wizardGenerateArticlePlan']);
    Route::post('/ai/wizard/generate-article-content', [AiController::class, 'wizardGenerateArticleContent']);
    Route::post('/ai/wizard/generate-image-prompt', [AiController::class, 'wizardGenerateImagePrompt']);
    Route::post('/ai/wizard/generate-image', [AiController::class, 'wizardGenerateImage']);
    Route::post('/ai/wizard/save-structure', [AiController::class, 'wizardSaveStructure']);
});

Route::get('/system/info', [SystemApiController::class, 'info']);
Route::post('/cache/clear', [SystemApiController::class, 'clearCache']);

if (file_exists(base_path('modules/vertex-forms/routes/api.php'))) {
    require base_path('modules/vertex-forms/routes/api.php');
}

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