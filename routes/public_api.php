<?php

use App\System\Http\Controllers\PublicSettingsApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/public')->group(function (): void {
    Route::get('/settings/site', [PublicSettingsApiController::class, 'site']);
});

