<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])
    ->prefix('admin/security')
    ->name('admin.security.')
    ->group(function () {
        // Dashboard and submodule routes will be attached here during runtime integration.
    });
