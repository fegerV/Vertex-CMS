<?php

use Vertex\Forms\Controllers\FormPublicController;
use Illuminate\Support\Facades\Route;

Route::get('/{form:slug}', [FormPublicController::class, 'show'])->name('show');
Route::get('/{form:slug}/config', [FormPublicController::class, 'config'])->name('config');
Route::post('/{form:slug}/draft', [FormPublicController::class, 'saveDraft'])->name('draft.save');
Route::get('/{form:slug}/draft/{token}', [FormPublicController::class, 'loadDraft'])->name('draft.load');
Route::get('/{form:slug}/results', [FormPublicController::class, 'results'])->name('results');
Route::post('/{form:slug}/submit', [FormPublicController::class, 'submit'])->name('submit');
