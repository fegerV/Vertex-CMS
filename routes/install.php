<?php

use App\System\Http\Controllers\InstallController;
use Illuminate\Support\Facades\Route;

Route::middleware('vertex.not_installed')->group(function (): void {
    Route::get('/install', [InstallController::class, 'index'])->name('install.index');
    Route::post('/install/check-requirements', [InstallController::class, 'checkRequirements'])->name('install.requirements');
    Route::post('/install/check-database', [InstallController::class, 'checkDatabase'])->name('install.database');
    Route::post('/install/save-config', [InstallController::class, 'saveConfig'])->name('install.config');
    Route::post('/install/run', [InstallController::class, 'run'])->name('install.run');
});

