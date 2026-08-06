<?php

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

// Настройки SEO
Route::get('seo/settings', [SeoDashboardController::class, 'settings'])
    ->middleware('vertex.permission:seo.settings')
    ->name('seo.settings');

Route::post('seo/settings/update', [SeoDashboardController::class, 'updateSettings'])
    ->middleware('vertex.permission:seo.settings')
    ->name('seo.settings.update');

// Редиректы
Route::get('seo/redirects', [RedirectController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('redirects.index');

Route::post('seo/redirects', [RedirectController::class, 'store'])
    ->middleware('vertex.permission:seo.edit')
    ->name('redirects.store');

Route::put('seo/redirects/{redirect}', [RedirectController::class, 'update'])
    ->middleware('vertex.permission:seo.edit')
    ->name('redirects.update');

Route::delete('seo/redirects/{redirect}', [RedirectController::class, 'destroy'])
    ->middleware('vertex.permission:seo.edit')
    ->name('redirects.destroy');
