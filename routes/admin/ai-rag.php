<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Seo\AiKnowledgeBaseController;
use App\Http\Controllers\Api\AiChatApiController;

/*
|--------------------------------------------------------------------------
| AI RAG Chat Routes
|--------------------------------------------------------------------------
*/

// Админ панель - управление базой знаний
Route::prefix('ai-kb')->name('ai-kb.')->group(function () {
    Route::get('/', [AiKnowledgeBaseController::class, 'index'])->name('index');
    Route::get('/categories', [AiKnowledgeBaseController::class, 'categories'])->name('categories');
    Route::post('/categories', [AiKnowledgeBaseController::class, 'storeCategory'])->name('categories.store');
    
    // Документы
    Route::get('/document/edit/{id?}', [AiKnowledgeBaseController::class, 'editDocument'])->name('document.edit');
    Route::post('/document/save', [AiKnowledgeBaseController::class, 'saveDocument'])->name('document.save');
    Route::delete('/document/delete/{id}', [AiKnowledgeBaseController::class, 'deleteDocument'])->name('document.delete');
    Route::post('/document/reprocess/{id}', [AiKnowledgeBaseController::class, 'reprocessDocument'])->name('document.reprocess');
    
    // Чаты
    Route::get('/chat-history', [AiKnowledgeBaseController::class, 'chatHistory'])->name('chat-history');
    Route::get('/chat-view/{id}', [AiKnowledgeBaseController::class, 'viewChat'])->name('chat-view');
    
    // Настройки
    Route::get('/settings', [AiKnowledgeBaseController::class, 'settings'])->name('settings');
    Route::post('/settings', [AiKnowledgeBaseController::class, 'saveSettings'])->name('settings.save');
});

/*
|--------------------------------------------------------------------------
| API Routes для AI чата
|--------------------------------------------------------------------------
*/
Route::prefix('ai/chat')->group(function () {
    Route::post('/', [AiChatApiController::class, 'chat']);
    Route::get('/history/{sessionId}', [AiChatApiController::class, 'history']);
    Route::post('/session', [AiChatApiController::class, 'newSession']);
});
