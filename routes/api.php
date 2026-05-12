<?php

use App\AI\Http\Controllers\AiController;
use App\Builder\Http\Controllers\BuilderApiController;
use App\Content\Http\Controllers\PageApiController;
use App\Media\Http\Controllers\MediaApiController;
use App\Media\Http\Controllers\MediaFolderApiController;
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
 Route::patch('/media/{media}/move', [MediaApiController::class, 'move']);
 Route::delete('/media/{media}', [MediaApiController::class, 'destroy']);

 // Media Folders
 Route::get('/media/folders', [MediaFolderApiController::class, 'index']);
 Route::post('/media/folders', [MediaFolderApiController::class, 'store']);
 Route::put('/media/folders/{folder}', [MediaFolderApiController::class, 'update']);
 Route::delete('/media/folders/{folder}', [MediaFolderApiController::class, 'destroy']);

Route::get('/builder/blocks', [BuilderApiController::class, 'blocks']);
Route::post('/builder/render-preview', [BuilderApiController::class, 'renderPreview']);
Route::get('/ai/providers', [AiController::class, 'providers']);
Route::post('/ai/chat', [AiController::class, 'chat']);

Route::get('/system/info', [SystemApiController::class, 'info']);
Route::post('/cache/clear', [SystemApiController::class, 'clearCache']);

