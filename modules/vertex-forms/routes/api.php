<?php

use Illuminate\Support\Facades\Route;
use Vertex\Forms\Controllers\FormApiController;

Route::prefix('forms')->name('api.forms.')->group(function (): void {
    Route::get('field-registry', [FormApiController::class, 'fieldRegistry'])
        ->middleware('vertex.permission:forms.view');

    Route::get('/', [FormApiController::class, 'index'])
        ->middleware('vertex.permission:forms.view');
    Route::post('/', [FormApiController::class, 'store'])
        ->middleware('vertex.permission:forms.create');
    Route::get('{form}', [FormApiController::class, 'show'])
        ->middleware('vertex.permission:forms.view');
    Route::put('{form}', [FormApiController::class, 'update'])
        ->middleware('vertex.permission:forms.edit');
    Route::delete('{form}', [FormApiController::class, 'destroy'])
        ->middleware('vertex.permission:forms.delete');
    Route::post('{form}/duplicate', [FormApiController::class, 'duplicate'])
        ->middleware('vertex.permission:forms.edit');
    Route::get('{form}/export-json', [FormApiController::class, 'exportJson'])
        ->middleware('vertex.permission:forms.view');
    Route::post('{form}/import-json', [FormApiController::class, 'importJson'])
        ->middleware('vertex.permission:forms.edit');
});
