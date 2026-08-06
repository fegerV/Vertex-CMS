<?php

use App\AI\Http\Controllers\AiController as DraftAiController;
use App\Builder\Http\Controllers\BuilderApiController;
use App\Content\Http\Controllers\PageApiController;
use App\Media\Http\Controllers\MediaApiController;
use App\Media\Http\Controllers\MediaFolderApiController;
use App\System\Http\Controllers\SystemApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Analytics\DashboardController;
use App\Http\Controllers\Analytics\HeatmapController;
use App\Http\Controllers\Api\AIController as ApiAiController;

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
Route::get('/ai/providers', [DraftAiController::class, 'providers']);
Route::post('/ai/chat', [DraftAiController::class, 'chat']);

// Site Wizard - AI-powered site creation
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/ai/wizard/generate-structure', [DraftAiController::class, 'wizardGenerateStructure']);
    Route::post('/ai/wizard/generate-semantic-core', [DraftAiController::class, 'wizardGenerateSemanticCore']);
    Route::post('/ai/wizard/generate-article-plan', [DraftAiController::class, 'wizardGenerateArticlePlan']);
    Route::post('/ai/wizard/generate-article-content', [DraftAiController::class, 'wizardGenerateArticleContent']);
    Route::post('/ai/wizard/generate-image-prompt', [DraftAiController::class, 'wizardGenerateImagePrompt']);
    Route::post('/ai/wizard/generate-image', [DraftAiController::class, 'wizardGenerateImage']);
    Route::post('/ai/wizard/save-structure', [DraftAiController::class, 'wizardSaveStructure']);
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
    Route::post('ai/chat', [ApiAiController::class, 'chat']);
    Route::post('ai/faq', [ApiAiController::class, 'faq']);
    Route::post('ai/generate', [ApiAiController::class, 'generateContent']);
    Route::post('ai/analyze-image', [ApiAiController::class, 'analyzeImage']);
    Route::post('ai/moderate', [ApiAiController::class, 'moderateContent']);
    Route::get('ai/search', [ApiAiController::class, 'search']);
    Route::get('ai/suggest', [ApiAiController::class, 'suggestQueries']);
    Route::post('ai/keywords', [ApiAiController::class, 'extractKeywords']);
    Route::post('ai/language', [ApiAiController::class, 'detectLanguage']);
    Route::post('ai/recommend', [ApiAiController::class, 'recommendProducts']);
});