<?php

use App\Builder\Http\Controllers\BuilderApiController;
use App\Content\Http\Controllers\PageApiController;
use App\Media\Http\Controllers\MediaApiController;
use App\System\Http\Controllers\SystemApiController;
use Illuminate\Support\Facades\Route;

Route::get('/pages', [PageApiController::class, 'index']);
Route::post('/pages', [PageApiController::class, 'store']);
Route::get('/pages/{page}', [PageApiController::class, 'show']);
Route::put('/pages/{page}', [PageApiController::class, 'update']);
Route::delete('/pages/{page}', [PageApiController::class, 'destroy']);

Route::get('/media', [MediaApiController::class, 'index']);
Route::post('/media/upload', [MediaApiController::class, 'store']);
Route::put('/media/{media}', [MediaApiController::class, 'update']);
Route::delete('/media/{media}', [MediaApiController::class, 'destroy']);

Route::get('/builder/blocks', [BuilderApiController::class, 'blocks']);
Route::post('/builder/render-preview', [BuilderApiController::class, 'renderPreview']);

Route::get('/system/info', [SystemApiController::class, 'info']);
Route::post('/cache/clear', [SystemApiController::class, 'clearCache']);

