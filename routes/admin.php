<?php

use App\Admin\Http\Controllers\DashboardController;
use App\Auth\Http\Controllers\AdminAuthController;
use App\Builder\Http\Controllers\PageBuilderController;
use App\Content\Http\Controllers\PageController;
use App\Media\Http\Controllers\MediaController;
use App\Seo\Http\Controllers\RedirectController;
use App\System\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->prefix('admin')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,15');
    });

    Route::middleware(['auth', 'vertex.permission:admin.access'])->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::resource('pages', PageController::class)->except(['show']);
        Route::get('pages/{page}/builder', [PageBuilderController::class, 'edit'])->name('pages.builder');
        Route::put('pages/{page}/builder', [PageBuilderController::class, 'update'])->name('pages.builder.update');

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media/upload', [MediaController::class, 'store'])->name('media.store');
        Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::resource('seo/redirects', RedirectController::class)->except(['show', 'create', 'edit']);

        Route::get('system/info', [SystemController::class, 'info'])->name('system.info');
        Route::get('system/logs', [SystemController::class, 'logs'])->name('system.logs');
        Route::get('system/cache', [SystemController::class, 'cache'])->name('system.cache');
        Route::post('system/cache/clear', [SystemController::class, 'clearCache'])->name('system.cache.clear');
    });
});
