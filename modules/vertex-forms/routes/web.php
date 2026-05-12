<?php

use Vertex\Forms\Controllers\FormPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/{form:slug}', [FormPublicController::class, 'config'])->name('show');
Route::post('/{form:slug}/submit', [FormPublicController::class, 'submit'])->name('submit');
