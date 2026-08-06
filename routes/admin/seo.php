<?php

use App\Http\Controllers\Admin\Seo\AiBrandMonitorController;
use App\Http\Controllers\Admin\Seo\SchemaController;
use App\Http\Controllers\Admin\Seo\SearchConsoleController;
use App\Http\Controllers\Admin\Seo\DuplicatesController;
use App\Http\Controllers\Admin\Seo\AiImagesController;
use App\Http\Controllers\Admin\Seo\SocialMediaController;
use App\Http\Controllers\Admin\Seo\ImageSeoController;
use App\Seo\Http\Controllers\RedirectController;
use App\Seo\Http\Controllers\RobotsController;
use App\Seo\Http\Controllers\SeoDashboardController;
use App\Seo\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// SEO Dashboard - Обзор
Route::get('seo', [SeoDashboardController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.dashboard');

// Анализ контента
Route::get('seo/analysis', [SeoDashboardController::class, 'analysis'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.analysis');

Route::post('seo/analyze-page', [SeoDashboardController::class, 'analyzePage'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.analyze-page');

// Массовое редактирование мета-тегов
Route::get('seo/bulk-editor', [SeoDashboardController::class, 'bulkEditor'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.bulk-editor');

Route::post('seo/bulk-update', [SeoDashboardController::class, 'bulkUpdate'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.bulk-update');

// Роботы и файлы (robots.txt, .htaccess, sitemap.xml)
Route::get('seo/files', [SeoDashboardController::class, 'files'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.files');

Route::get('seo/robots/edit', [RobotsController::class, 'edit'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.robots.edit');

Route::post('seo/robots/update', [RobotsController::class, 'update'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.robots.update');

Route::get('seo/sitemap/generate', [SitemapController::class, 'generate'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.sitemap.generate');

// Семантическое ядро
Route::get('seo/semantics', [SeoDashboardController::class, 'semantics'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.semantics');

Route::post('seo/keywords', [SeoDashboardController::class, 'addKeyword'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.keywords.add');

Route::delete('seo/keywords/{keyword}', [SeoDashboardController::class, 'deleteKeyword'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.keywords.delete');

// Внутренние ссылки
Route::get('seo/internal-links', [SeoDashboardController::class, 'internalLinks'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.internal-links');

Route::post('seo/internal-links/suggest', [SeoDashboardController::class, 'suggestLinks'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.internal-links.suggest');

Route::get('seo/orphan-pages', [SeoDashboardController::class, 'orphanPages'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.orphan-pages');

// AI-Ассистент
Route::get('seo/ai-assistant', [SeoDashboardController::class, 'aiAssistant'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.ai-assistant');

Route::post('seo/ai/generate-meta', [SeoDashboardController::class, 'generateMetaTags'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.ai.generate-meta');

Route::post('seo/ai/generate-content', [SeoDashboardController::class, 'generateContent'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.ai.generate-content');

// 404 Monitor и Редиректы (Manager)
Route::get('seo/redirects', [RedirectController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.redirects');

Route::post('seo/redirects', [RedirectController::class, 'store'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.redirects.store');

Route::put('seo/redirects/{redirect}', [RedirectController::class, 'update'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.redirects.update');

Route::delete('seo/redirects/{redirect}', [RedirectController::class, 'destroy'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.redirects.destroy');

Route::get('seo/redirects/logs', [RedirectController::class, 'logs'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.redirects.logs');

Route::post('seo/redirects/import-404', [RedirectController::class, 'importFromLogs'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.redirects.import-404');

Route::post('seo/redirects/bulk-import', [RedirectController::class, 'bulkImport'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.redirects.bulk-import');

// Конструктор Schema.org
Route::get('seo/schema-builder', [SchemaController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.schema-builder');

Route::post('seo/schema-builder/generate', [SchemaController::class, 'generate'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.schema-builder.generate');

Route::post('seo/schema-builder/save', [SchemaController::class, 'saveToPage'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.schema-builder.save');

// Интеграция с Search Console
Route::get('seo/search-console', [SearchConsoleController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.search-console');

Route::post('seo/search-console/connect', [SearchConsoleController::class, 'connect'])
    ->middleware('vertex.permission:seo.settings')
    ->name('seo.search-console.connect');

Route::get('seo/search-console/queries', [SearchConsoleController::class, 'queries'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.search-console.queries');

Route::get('seo/search-console/errors', [SearchConsoleController::class, 'errors'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.search-console.errors');

// Поиск дубликатов контента
Route::get('seo/duplicates', [DuplicatesController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.duplicates');

Route::post('seo/duplicates/scan', [DuplicatesController::class, 'scan'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.duplicates.scan');

// AI Генерация Alt-текстов для изображений
Route::get('seo/ai-images', [AiImagesController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.ai-images');

Route::post('seo/ai-images/generate', [AiImagesController::class, 'generate'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.ai-images.generate');

Route::post('seo/ai-images/bulk-generate', [AiImagesController::class, 'bulkGenerate'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.ai-images.bulk-generate');

// Социальные сети и Open Graph
Route::get('seo/social-media', [SocialMediaController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.social-media');

Route::post('seo/social-media/update', [SocialMediaController::class, 'update'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.social.update');

Route::post('seo/social-media/upload-image', [SocialMediaController::class, 'uploadImage'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.social.upload-image');

Route::post('seo/social-media/preview', [SocialMediaController::class, 'preview'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.social.preview');

Route::get('seo/social-media/page-meta/{page}', [SocialMediaController::class, 'generatePageMeta'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.social.page-meta');

// AI Мониторинг Бренда
Route::get('seo/ai-monitor', [AiBrandMonitorController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.ai-monitor.index');

Route::post('seo/ai-monitor/refresh', [AiBrandMonitorController::class, 'refresh'])
    ->middleware('vertex.permission:seo.edit')
    ->name('seo.ai-monitor.refresh');

Route::get('seo/ai-monitor/mentions', [AiBrandMonitorController::class, 'mentions'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.ai-monitor.mentions');

Route::get('seo/ai-monitor/sources', [AiBrandMonitorController::class, 'sources'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.ai-monitor.sources');

Route::get('seo/ai-monitor/competitors', [AiBrandMonitorController::class, 'competitors'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.ai-monitor.competitors');

Route::get('seo/ai-monitor/opportunities', [AiBrandMonitorController::class, 'opportunities'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.ai-monitor.opportunities');

// Настройки SEO
Route::get('seo/settings', [SeoDashboardController::class, 'settings'])
    ->middleware('vertex.permission:seo.settings')
    ->name('seo.settings');

Route::post('seo/settings/update', [SeoDashboardController::class, 'updateSettings'])
    ->middleware('vertex.permission:seo.settings')
    ->name('seo.settings.update');

// ==========================================
// Image SEO Analyzer Routes
// ==========================================
Route::prefix('images')->name('images.')->group(function () {
    Route::get('/', [ImageSeoController::class, 'index'])->name('index');
    Route::post('/update-alt', [ImageSeoController::class, 'updateAlt'])->name('update-alt');
    Route::post('/generate-alt', [ImageSeoController::class, 'generateAltAi'])->name('generate-alt');
    Route::post('/lazy-load', [ImageSeoController::class, 'enableLazyLoad'])->name('lazy-load');
    Route::post('/compress', [ImageSeoController::class, 'compressImages'])->name('compress');
    Route::post('/sitemap', [ImageSeoController::class, 'generateImageSitemap'])->name('sitemap');
});
