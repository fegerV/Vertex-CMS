<?php

use App\Seo\Http\Controllers\RedirectController;
use App\Seo\Http\Controllers\SeoDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('seo', [SeoDashboardController::class, 'index'])
    ->middleware('vertex.permission:seo.view')
    ->name('seo.dashboard');

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
