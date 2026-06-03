<?php

use App\Security\Login\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

// Two-Factor Authentication
Route::get('2fa/verify', [TwoFactorController::class, 'show'])->name('2fa.verify');
Route::post('2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.submit');