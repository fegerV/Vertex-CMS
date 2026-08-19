<?php

use App\Admin\Http\Controllers\SettingsController;
use App\System\Http\Controllers\SecurityDashboardController;
use App\System\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

// Settings
Route::get("settings", [SettingsController::class, "edit"])->middleware("vertex.permission:settings.view")->name("settings.edit");
Route::put("settings", [SettingsController::class, "update"])->middleware("vertex.permission:settings.edit")->name("settings.update");

// System
Route::get("system/info", [SystemController::class, "info"])->middleware("vertex.permission:system.view")->name("system.info");
Route::get("system/security", [SecurityDashboardController::class, "index"])->middleware("vertex.permission:system.view")->name("system.security");
Route::post("system/security/integrity/baseline", [SecurityDashboardController::class, "initializeIntegrityBaseline"])->middleware("vertex.permission:system.view")->name("system.security.integrity.baseline");
Route::post("system/security/integrity/scan", [SecurityDashboardController::class, "runIntegrityScan"])->middleware("vertex.permission:system.view")->name("system.security.integrity.scan");
Route::get("system/logs", [SystemController::class, "logs"])->middleware("vertex.permission:system.view")->name("system.logs");
Route::get("system/analytics", [SystemController::class, "analytics"])->middleware("vertex.permission:analytics.view")->name("system.analytics");
Route::get("system/cache", [SystemController::class, "cache"])->middleware("vertex.permission:system.view")->name("system.cache");
Route::post("system/cache/clear", [SystemController::class, "clearCache"])->middleware("vertex.permission:cache.clear")->name("system.cache.clear");
