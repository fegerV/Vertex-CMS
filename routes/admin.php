<?php

use App\Admin\Http\Controllers\DashboardController;
use App\AI\Http\Controllers\AiController as DraftAiController;
use App\Admin\Http\Controllers\SettingsController;
use App\Builder\Http\Controllers\BuilderApiController;
use App\Content\Http\Controllers\CustomFieldGroupController;
use App\Core\Http\Middleware\SetAdminLocale;
use App\Ecommerce\Http\Controllers\OrderController;
use App\Ecommerce\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Admin\WebhookController;
use App\Media\Http\Controllers\MediaController;
use App\Media\Http\Controllers\MediaApiController;
use App\Media\Http\Controllers\MediaFolderApiController;
use App\Security\Login\Http\Controllers\LoginController;
use App\Security\Login\Http\Controllers\TwoFactorController;
use App\System\Http\Controllers\QueueController;
use App\System\Http\Controllers\SecurityController;
use App\System\Http\Controllers\SystemController;
use App\System\Http\Controllers\DashboardStatsController;
use Illuminate\Support\Facades\Route;
use Vertex\Forms\Controllers\FormApiController;

Route::name('admin.')->prefix('admin')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,5');
    });

    // Two-Factor Authentication (requires auth but not yet 2FA verified)
    Route::middleware(['auth', 'login.2fa', SetAdminLocale::class])->group(function (): void {
        Route::get('2fa/verify', [TwoFactorController::class, 'show'])->name('2fa.verify');
        Route::post('2fa/verify', [TwoFactorController::class, 'verify'])->name('2fa.verify.submit');
    });

    // Already fully authenticated (passport + optional 2FA)
    Route::middleware([
        'auth',
        SetAdminLocale::class,
        'login.2fa',
        'login.password.expiry',
    ])->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/locale/{locale}', function (string $locale) {
            abort_unless(in_array($locale, ['en', 'ru'], true), 404);

            session(['admin_locale' => $locale]);

            return back();
        })->name('locale.change');

        // Modular route files
        require __DIR__.'/admin/pages.php';
        require __DIR__.'/admin/users.php';
        require __DIR__.'/admin/taxonomies.php';
        require __DIR__.'/admin/email.php';
        require __DIR__.'/admin/system.php';
        require __DIR__.'/admin/seo.php';
        require __DIR__.'/admin/ai-rag.php';

        Route::get('integrations/webhooks', [WebhookController::class, 'index'])->middleware('vertex.permission:settings.view')->name('webhooks.index');
        Route::post('integrations/webhooks', [WebhookController::class, 'store'])->middleware('vertex.permission:settings.edit')->name('webhooks.store');
        Route::put('integrations/webhooks/{webhook}', [WebhookController::class, 'update'])->middleware('vertex.permission:settings.edit')->name('webhooks.update');
        Route::delete('integrations/webhooks/{webhook}', [WebhookController::class, 'destroy'])->middleware('vertex.permission:settings.edit')->name('webhooks.destroy');
        Route::post('integrations/webhooks/{webhook}/test', [WebhookController::class, 'test'])->middleware('vertex.permission:settings.edit')->name('webhooks.test');

        // Custom Field Groups
        Route::get('custom-field-groups', [CustomFieldGroupController::class, 'index'])->middleware('vertex.permission:pages.edit')->name('custom-field-groups.index');
        Route::post('custom-field-groups', [CustomFieldGroupController::class, 'store'])->middleware('vertex.permission:pages.edit')->name('custom-field-groups.store');
        Route::put('custom-field-groups/{customFieldGroup}', [CustomFieldGroupController::class, 'update'])->middleware('vertex.permission:pages.edit')->name('custom-field-groups.update');
        Route::delete('custom-field-groups/{customFieldGroup}', [CustomFieldGroupController::class, 'destroy'])->middleware('vertex.permission:pages.edit')->name('custom-field-groups.destroy');

        // Media
        Route::get('media', [MediaController::class, 'index'])->middleware('vertex.permission:media.view')->name('media.index');
        Route::post('media/upload', [MediaController::class, 'store'])->middleware('vertex.permission:media.upload')->name('media.store');
        Route::put('media/{media}', [MediaController::class, 'update'])->middleware('vertex.permission:media.edit')->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->middleware('vertex.permission:media.delete')->name('media.destroy');

        // Forms module routes (vertex-forms)
        require base_path('modules/vertex-forms/routes/admin.php');

        // Builder API routes
        Route::get('api/builder/blocks', [BuilderApiController::class, 'blocks'])
            ->middleware('vertex.permission:pages.edit')
            ->name('api.builder.blocks');
        Route::post('api/builder/render-preview', [BuilderApiController::class, 'renderPreview'])
            ->middleware('vertex.permission:pages.edit')
            ->name('api.builder.render-preview');

        // JSON endpoints consumed by the admin UI (session authenticated).
        Route::prefix('api')->name('api.')->group(function (): void {
            Route::get('dashboard/stats', [DashboardStatsController::class, 'index'])
                ->name('dashboard.stats');

            // Media library + folders bridge used by the builder and media manager.
            Route::get('media', [MediaApiController::class, 'index'])->name('media.index');
            Route::post('media/upload', [MediaApiController::class, 'store'])->name('media.store');
            Route::put('media/{media}', [MediaApiController::class, 'update'])->name('media.update');
            Route::patch('media/{media}/move', [MediaApiController::class, 'move'])->name('media.move');
            Route::delete('media/{media}', [MediaApiController::class, 'destroy'])->name('media.destroy');
            Route::get('media/folders', [MediaFolderApiController::class, 'index'])->name('media.folders.index');
            Route::post('media/folders', [MediaFolderApiController::class, 'store'])->name('media.folders.store');
            Route::put('media/folders/{folder}', [MediaFolderApiController::class, 'update'])->name('media.folders.update');
            Route::delete('media/folders/{folder}', [MediaFolderApiController::class, 'destroy'])->name('media.folders.destroy');

            // AI draft assistant panel endpoints.
            Route::get('ai/providers', [DraftAiController::class, 'providers'])->name('ai.providers');
            Route::post('ai/chat', [DraftAiController::class, 'chat'])->name('ai.chat');
            Route::post('ai/edit', [DraftAiController::class, 'chat'])->name('ai.edit');

            // Forms builder API (vertex-forms).
            Route::get('forms/field-registry', [FormApiController::class, 'fieldRegistry'])
                ->middleware('vertex.permission:forms.view')
                ->name('forms.field-registry');
            Route::get('forms', [FormApiController::class, 'index'])
                ->middleware('vertex.permission:forms.view')
                ->name('forms.index');
            Route::post('forms', [FormApiController::class, 'store'])
                ->middleware('vertex.permission:forms.create')
                ->name('forms.store');
            Route::get('forms/{form}', [FormApiController::class, 'show'])
                ->middleware('vertex.permission:forms.view')
                ->name('forms.show');
            Route::put('forms/{form}', [FormApiController::class, 'update'])
                ->middleware('vertex.permission:forms.edit')
                ->name('forms.update');
            Route::delete('forms/{form}', [FormApiController::class, 'destroy'])
                ->middleware('vertex.permission:forms.delete')
                ->name('forms.destroy');
            Route::post('forms/{form}/duplicate', [FormApiController::class, 'duplicate'])
                ->middleware('vertex.permission:forms.edit')
                ->name('forms.duplicate');
            Route::get('forms/{form}/export-json', [FormApiController::class, 'exportJson'])
                ->middleware('vertex.permission:forms.import_export')
                ->name('forms.export-json');
            Route::post('forms/{form}/import-json', [FormApiController::class, 'importJson'])
                ->middleware('vertex.permission:forms.import_export')
                ->name('forms.import-json');
        });

        Route::get('custom-field-groups', [CustomFieldGroupController::class, 'index'])
            ->middleware('vertex.permission:pages.edit')
            ->name('custom-field-groups.index');
        Route::post('custom-field-groups', [CustomFieldGroupController::class, 'store'])
            ->middleware('vertex.permission:pages.edit')
            ->name('custom-field-groups.store');
        Route::put('custom-field-groups/{customFieldGroup}', [CustomFieldGroupController::class, 'update'])
            ->middleware('vertex.permission:pages.edit')
            ->name('custom-field-groups.update');
        Route::delete('custom-field-groups/{customFieldGroup}', [CustomFieldGroupController::class, 'destroy'])
            ->middleware('vertex.permission:pages.edit')
            ->name('custom-field-groups.destroy');
        Route::get('media', [MediaController::class, 'index'])
            ->middleware('vertex.permission:media.view')
            ->name('media.index');
        Route::post('media/upload', [MediaController::class, 'store'])
            ->middleware('vertex.permission:media.upload')
            ->name('media.store');
        Route::put('media/{media}', [MediaController::class, 'update'])
            ->middleware('vertex.permission:media.edit')
            ->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])
            ->middleware('vertex.permission:media.delete')
            ->name('media.destroy');

        // Массовые операции
        Route::post('media/bulk-delete', [MediaController::class, 'bulkDelete'])
            ->middleware('vertex.permission:media.delete')
            ->name('media.bulk-delete');
        Route::post('media/bulk-move', [MediaController::class, 'bulkMove'])
            ->middleware('vertex.permission:media.edit')
            ->name('media.bulk-move');

        // Версии файлов
        Route::get('media/{media}/versions', [MediaController::class, 'versions'])
            ->middleware('vertex.permission:media.view')
            ->name('media.versions');
        Route::post('media/versions/{version}/revert', [MediaController::class, 'revertVersion'])
            ->middleware('vertex.permission:media.edit')
            ->name('media.revert-version');

        // Использование файла
        Route::get('media/{media}/usage', [MediaController::class, 'usageStats'])
            ->middleware('vertex.permission:media.view')
            ->name('media.usage');

        // Оптимизация
        Route::post('media/{media}/optimize', [MediaController::class, 'optimize'])
            ->middleware('vertex.permission:media.edit')
            ->name('media.optimize');

        // Теги
        Route::get('media/tags', [MediaController::class, 'tags'])
            ->middleware('vertex.permission:media.view')
            ->name('media.tags');

        Route::get('system/info', [SystemController::class, 'info'])
            ->middleware('vertex.permission:system.view')
            ->name('system.info');
        Route::get('system/logs', [SystemController::class, 'logs'])
            ->middleware('vertex.permission:system.view')
            ->name('system.logs');
        Route::get('system/cache', [SystemController::class, 'cache'])
            ->middleware('vertex.permission:system.view')
            ->name('system.cache');
        Route::post('system/cache/clear', [SystemController::class, 'clearCache'])
            ->middleware('vertex.permission:cache.clear')
            ->name('system.cache.clear');

        // Queue monitoring routes
        Route::get('system/queues', [QueueController::class, 'index'])
            ->middleware('vertex.permission:system.view')
            ->name('system.queues');
        Route::get('system/queues/{queue}', [QueueController::class, 'show'])
            ->middleware('vertex.permission:system.view')
            ->name('system.queues.show');
        Route::post('system/queues/failed/{id}/retry', [QueueController::class, 'retryFailed'])
            ->middleware('vertex.permission:system.edit')
            ->name('system.queues.retry-failed');
        Route::post('system/queues/failed/{id}/delete', [QueueController::class, 'deleteFailed'])
            ->middleware('vertex.permission:system.edit')
            ->name('system.queues.delete-failed');
        Route::post('system/queues/failed/flush', [QueueController::class, 'flushFailed'])
            ->middleware('vertex.permission:system.edit')
            ->name('system.queues.flush-failed');

        // Security routes (GDPR & IP Filters)
        Route::get('security/gdpr', [SecurityController::class, 'gdpr'])
            ->middleware('vertex.permission:settings.edit')
            ->name('security.gdpr');
        Route::post('security/gdpr', [SecurityController::class, 'updateGdpr'])
            ->middleware('vertex.permission:settings.edit')
            ->name('security.gdpr.update');
        Route::get('security/ip-filters', [SecurityController::class, 'ipFilters'])
            ->middleware('vertex.permission:settings.edit')
            ->name('security.ip-filters');
        Route::post('security/ip-filters', [SecurityController::class, 'storeIpFilter'])
            ->middleware('vertex.permission:settings.edit')
            ->name('security.ip-filters.store');
        Route::put('security/ip-filters/{ipFilter}', [SecurityController::class, 'updateIpFilter'])
            ->middleware('vertex.permission:settings.edit')
            ->name('security.ip-filters.update');
        Route::delete('security/ip-filters/{ipFilter}', [SecurityController::class, 'destroyIpFilter'])
            ->middleware('vertex.permission:settings.edit')
            ->name('security.ip-filters.destroy');

        // Backup routes
        Route::get('system/backups', [BackupController::class, 'index'])
            ->middleware('vertex.permission:system.view')
            ->name('system.backups');
        Route::get('api/backups', [BackupController::class, 'apiList'])
            ->middleware('vertex.permission:system.view')
            ->name('api.backups.list');
        Route::post('api/backups/create', [BackupController::class, 'apiCreate'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.backups.create');
        Route::get('api/backups/download/{filename}', [BackupController::class, 'apiDownload'])
            ->middleware('vertex.permission:system.view')
            ->name('api.backups.download');
        Route::post('api/backups/restore', [BackupController::class, 'apiRestore'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.backups.restore');
        Route::delete('api/backups/{filename}', [BackupController::class, 'apiDelete'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.backups.delete');
        Route::get('api/backup-schedule', [BackupController::class, 'getSchedule'])
            ->middleware('vertex.permission:system.view')
            ->name('api.backup.schedule.get');
        Route::post('api/backup-schedule', [BackupController::class, 'saveSchedule'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.backup.schedule.save');

        // Queue monitoring API routes
        Route::get('api/queues/stats', [App\Http\Controllers\Admin\QueueController::class, 'apiStats'])
            ->middleware('vertex.permission:system.view')
            ->name('api.queues.stats');
        Route::get('api/queues/workers', [App\Http\Controllers\Admin\QueueController::class, 'apiWorkerStatus'])
            ->middleware('vertex.permission:system.view')
            ->name('api.queues.workers');
        Route::post('api/queues/failed/{id}/retry', [App\Http\Controllers\Admin\QueueController::class, 'apiRetryFailed'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.queues.retry-failed');
        Route::post('api/queues/failed/retry-all', [App\Http\Controllers\Admin\QueueController::class, 'apiRetryAllFailed'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.queues.retry-all');
        Route::delete('api/queues/failed/{id}', [App\Http\Controllers\Admin\QueueController::class, 'apiDeleteFailed'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.queues.delete-failed');
        Route::post('api/queues/clear', [App\Http\Controllers\Admin\QueueController::class, 'apiClearQueue'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.queues.clear');
    });
});

// System Updates
Route::prefix('system')->name('system.')->group(function () {
    Route::get('/updates', [UpdateController::class, 'index'])->name('updates.index');
    Route::get('/updates/check', [UpdateController::class, 'check'])->name('updates.check');
    Route::post('/updates/update', [UpdateController::class, 'update'])->name('updates.update');
    Route::post('/optimize', [UpdateController::class, 'optimize'])->name('optimize');
});

Route::prefix('admin/ecommerce')->name('admin.ecommerce.')->middleware([
    'auth',
    SetAdminLocale::class,
    'login.2fa',
    'login.password.expiry',
])->group(function (): void {
    Route::get('products', [ProductController::class, 'index'])
        ->middleware('vertex.permission:ecommerce.products.view')->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])
        ->middleware('vertex.permission:ecommerce.products.create')->name('products.create');
    Route::post('products', [ProductController::class, 'store'])
        ->middleware('vertex.permission:ecommerce.products.create')->name('products.store');
    Route::get('products/{product}', [ProductController::class, 'show'])
        ->middleware('vertex.permission:ecommerce.products.view')->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])
        ->middleware('vertex.permission:ecommerce.products.edit')->name('products.edit');
    Route::put('products/{product}', [ProductController::class, 'update'])
        ->middleware('vertex.permission:ecommerce.products.edit')->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])
        ->middleware('vertex.permission:ecommerce.products.delete')->name('products.destroy');

    Route::get('orders', [OrderController::class, 'index'])
        ->middleware('vertex.permission:ecommerce.orders.view')->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])
        ->middleware('vertex.permission:ecommerce.orders.view')->name('orders.show');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])
        ->middleware('vertex.permission:ecommerce.orders.update')->name('orders.update-status');
    Route::post('orders/{order}/payment', [OrderController::class, 'updatePayment'])
        ->middleware('vertex.permission:ecommerce.payments.update')->name('orders.update-payment');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])
        ->middleware('vertex.permission:ecommerce.orders.update')->name('orders.cancel');
    Route::post('orders/{order}/refund', [OrderController::class, 'refund'])
        ->middleware('vertex.permission:ecommerce.payments.update')->name('orders.refund');
});

// Settings routes
Route::middleware(['auth', 'vertex.permission:settings.view'])->group(function (): void {
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
});
