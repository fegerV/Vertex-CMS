<?php

use App\Admin\Http\Controllers\DashboardController;
use App\Admin\Http\Controllers\SettingsController;
use App\Auth\Http\Controllers\AdminAuthController;
use App\Builder\Http\Controllers\AdvancedBuilderController;
use App\Builder\Http\Controllers\BuilderApiController;
use App\Builder\Http\Controllers\PageBuilderController;
use App\Content\Http\Controllers\CustomFieldGroupController;
use App\Core\Http\Middleware\SetAdminLocale;
use App\Media\Http\Controllers\MediaController;
use App\Seo\Http\Controllers\RedirectController;
use App\System\Http\Controllers\QueueController;
use App\System\Http\Controllers\SecurityController;
use App\System\Http\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::name("admin.")->prefix("admin")->group(function (): void {
     Route::middleware("guest")->group(function (): void {
         Route::get("/login", [AdminAuthController::class, "showLogin"])->name("login");
         Route::post("/login", [AdminAuthController::class, "login"])->middleware("throttle:10,5");
     });

     // Two-Factor Authentication (requires auth but not yet 2FA verified)
     Route::middleware(["auth", "login.2fa", SetAdminLocale::class])->group(function (): void {
         Route::get("2fa/verify", [TwoFactorController::class, "show"])->name("2fa.verify");
         Route::post("2fa/verify", [TwoFactorController::class, "verify"])->name("2fa.verify.submit");
     });

     // Already fully authenticated (passport + optional 2FA)
     Route::middleware([
         "auth",
         SetAdminLocale::class,
         "login.password.expiry",
     ])->group(function (): void {
        Route::get("/", [DashboardController::class, "index"])->name("dashboard");
        Route::post("/logout", [AdminAuthController::class, "logout"])->name("logout");

        // Modular route files
        require __DIR__ . "/admin/pages.php";
        require __DIR__ . "/admin/users.php";
        require __DIR__ . "/admin/taxonomies.php";
        require __DIR__ . "/admin/email.php";
        require __DIR__ . "/admin/system.php";
        require __DIR__ . "/admin/seo.php";

        // Custom Field Groups
        Route::get("custom-field-groups", [CustomFieldGroupController::class, "index"])->middleware("vertex.permission:pages.edit")->name("custom-field-groups.index");
        Route::post("custom-field-groups", [CustomFieldGroupController::class, "store"])->middleware("vertex.permission:pages.edit")->name("custom-field-groups.store");
        Route::put("custom-field-groups/{customFieldGroup}", [CustomFieldGroupController::class, "update"])->middleware("vertex.permission:pages.edit")->name("custom-field-groups.update");
        Route::delete("custom-field-groups/{customFieldGroup}", [CustomFieldGroupController::class, "destroy"])->middleware("vertex.permission:pages.edit")->name("custom-field-groups.destroy");

        // Media
        Route::get("media", [MediaController::class, "index"])->middleware("vertex.permission:media.view")->name("media.index");
        Route::post("media/upload", [MediaController::class, "store"])->middleware("vertex.permission:media.upload")->name("media.store");
        Route::put("media/{media}", [MediaController::class, "update"])->middleware("vertex.permission:media.edit")->name("media.update");
        Route::delete("media/{media}", [MediaController::class, "destroy"])->middleware("vertex.permission:media.delete")->name("media.destroy");

        // Forms module routes (vertex-forms)
        require base_path('modules/vertex-forms/routes/admin.php');

        // Builder routes
        Route::get('pages', [PageController::class, 'index'])
            ->middleware('vertex.permission:pages.view')
            ->name('pages.index');
        Route::get('pages/create', [PageController::class, 'create'])
            ->middleware('vertex.permission:pages.create')
            ->name('pages.create');
        Route::post('pages', [PageController::class, 'store'])
            ->middleware('vertex.permission:pages.create')
            ->name('pages.store');
        Route::get('pages/{page}/edit', [PageController::class, 'edit'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.edit');
        Route::put('pages/{page}', [PageController::class, 'update'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.update');
        Route::delete('pages/{page}', [PageController::class, 'destroy'])
            ->middleware('vertex.permission:pages.delete')
            ->name('pages.destroy');
        Route::get('pages/{page}/builder', [PageBuilderController::class, 'edit'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder');
        Route::put('pages/{page}/builder', [PageBuilderController::class, 'update'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.update');
        Route::get('pages/{page}/builder/advanced', [AdvancedBuilderController::class, 'advanced'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.advanced');
        Route::post('pages/{page}/builder/advanced/save', [AdvancedBuilderController::class, 'saveAdvanced'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.advanced.save');
        Route::post('pages/{page}/builder/advanced/auto-save', [AdvancedBuilderController::class, 'autoSave'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.advanced.autosave');
        Route::post('pages/{page}/builder/preview', [PageBuilderController::class, 'preview'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.preview');
        Route::post('pages/{page}/revisions/{revision}/restore', [AdvancedBuilderController::class, 'restoreRevision'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.revisions.restore');
        Route::get('pages/{page}/revisions/compare/{revisionA}/{revisionB}', [AdvancedBuilderController::class, 'compareRevisions'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.revisions.compare');
        Route::get('pages/{page}/revisions', [AdvancedBuilderController::class, 'getRevisions'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.revisions');
        Route::post('pages/export-sections', [AdvancedBuilderController::class, 'exportSections'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.export-sections');
        Route::post('pages/import-sections', [AdvancedBuilderController::class, 'importSections'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.import-sections');
        Route::get('pages/templates', [AdvancedBuilderController::class, 'getTemplates'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.templates');
        Route::post('pages/{page}/apply-template', [AdvancedBuilderController::class, 'applyTemplate'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.apply-template');
        Route::post('pages/{page}/builder/template', [AdvancedBuilderController::class, 'applyTemplate'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.template');
        Route::post('pages/{page}/builder/auto-save', [AdvancedBuilderController::class, 'autoSave'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.autosave');
        Route::get('pages/{page}/search', [AdvancedBuilderController::class, 'searchContent'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.search');

        // Builder API routes
        Route::get('api/builder/blocks', [BuilderApiController::class, 'blocks'])
            ->middleware('vertex.permission:pages.edit')
            ->name('api.builder.blocks');
        Route::post('api/builder/render-preview', [BuilderApiController::class, 'renderPreview'])
            ->middleware('vertex.permission:pages.edit')
            ->name('api.builder.render-preview');

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

        Route::get('seo/redirects', [RedirectController::class, 'index'])
            ->middleware('vertex.permission:seo.view')
            ->name('redirects.index');
        Route::post('seo/redirects', [RedirectController::class, 'store'])
            ->middleware('vertex.permission:seo.edit')
            ->name('redirects.store');
        Route::put('seo/redirects/{redirect}', [RedirectController::class, 'update'])
            ->middleware('vertex.permission:seo.edit')
            ->name('redirects.update');
        Route::delete('seo/redirects/{redirect}', [RedirectController::class, 'destroy'])
            ->middleware('vertex.permission:seo.edit')
            ->name('redirects.destroy');

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
        Route::get('system/backups', [\App\Http\Controllers\Admin\BackupController::class, 'index'])
            ->middleware('vertex.permission:system.view')
            ->name('system.backups');
        Route::get('api/backups', [\App\Http\Controllers\Admin\BackupController::class, 'apiList'])
            ->middleware('vertex.permission:system.view')
            ->name('api.backups.list');
        Route::post('api/backups/create', [\App\Http\Controllers\Admin\BackupController::class, 'apiCreate'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.backups.create');
        Route::get('api/backups/download/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'apiDownload'])
            ->middleware('vertex.permission:system.view')
            ->name('api.backups.download');
        Route::post('api/backups/restore', [\App\Http\Controllers\Admin\BackupController::class, 'apiRestore'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.backups.restore');
        Route::delete('api/backups/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'apiDelete'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.backups.delete');
        Route::get('api/backup-schedule', [\App\Http\Controllers\Admin\BackupController::class, 'getSchedule'])
            ->middleware('vertex.permission:system.view')
            ->name('api.backup.schedule.get');
        Route::post('api/backup-schedule', [\App\Http\Controllers\Admin\BackupController::class, 'saveSchedule'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.backup.schedule.save');

        // Queue monitoring API routes
        Route::get('api/queues/stats', [\App\Http\Controllers\Admin\QueueController::class, 'apiStats'])
            ->middleware('vertex.permission:system.view')
            ->name('api.queues.stats');
        Route::get('api/queues/workers', [\App\Http\Controllers\Admin\QueueController::class, 'apiWorkerStatus'])
            ->middleware('vertex.permission:system.view')
            ->name('api.queues.workers');
        Route::post('api/queues/failed/{id}/retry', [\App\Http\Controllers\Admin\QueueController::class, 'apiRetryFailed'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.queues.retry-failed');
        Route::post('api/queues/failed/retry-all', [\App\Http\Controllers\Admin\QueueController::class, 'apiRetryAllFailed'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.queues.retry-all');
        Route::delete('api/queues/failed/{id}', [\App\Http\Controllers\Admin\QueueController::class, 'apiDeleteFailed'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.queues.delete-failed');
        Route::post('api/queues/clear', [\App\Http\Controllers\Admin\QueueController::class, 'apiClearQueue'])
            ->middleware('vertex.permission:system.edit')
            ->name('api.queues.clear');
     });
});

// System Updates
Route::prefix('system')->name('system.')->group(function () {
    Route::get('/updates', [App\Http\Controllers\Admin\UpdateController::class, 'index'])->name('updates.index');
    Route::get('/updates/check', [App\Http\Controllers\Admin\UpdateController::class, 'check'])->name('updates.check');
    Route::post('/updates/update', [App\Http\Controllers\Admin\UpdateController::class, 'update'])->name('updates.update');
    Route::post('/optimize', [App\Http\Controllers\Admin\UpdateController::class, 'optimize'])->name('optimize');
});

// E-commerce routes
Route::middleware(['auth', 'vertex.permission:admin.access'])->group(function (): void {
    // Products
    Route::get('ecommerce/products', [App\Ecommerce\Http\Controllers\ProductController::class, 'index'])
        ->name('ecommerce.products.index');
    Route::get('ecommerce/products/create', [App\Ecommerce\Http\Controllers\ProductController::class, 'create'])
        ->name('ecommerce.products.create');
    Route::post('ecommerce/products', [App\Ecommerce\Http\Controllers\ProductController::class, 'store'])
        ->name('ecommerce.products.store');
    Route::get('ecommerce/products/{product}', [App\Ecommerce\Http\Controllers\ProductController::class, 'show'])
        ->name('ecommerce.products.show');
    Route::get('ecommerce/products/{product}/edit', [App\Ecommerce\Http\Controllers\ProductController::class, 'edit'])
        ->name('ecommerce.products.edit');
    Route::put('ecommerce/products/{product}', [App\Ecommerce\Http\Controllers\ProductController::class, 'update'])
        ->name('ecommerce.products.update');
    Route::delete('ecommerce/products/{product}', [App\Ecommerce\Http\Controllers\ProductController::class, 'destroy'])
        ->name('ecommerce.products.destroy');

    // Orders
    Route::get('ecommerce/orders', [App\Ecommerce\Http\Controllers\OrderController::class, 'index'])
        ->name('ecommerce.orders.index');
    Route::get('ecommerce/orders/{order}', [App\Ecommerce\Http\Controllers\OrderController::class, 'show'])
        ->name('ecommerce.orders.show');
    Route::post('ecommerce/orders/{order}/status', [App\Ecommerce\Http\Controllers\OrderApiController::class, 'updateStatus'])
        ->name('ecommerce.orders.update-status');
    Route::post('ecommerce/orders/{order}/cancel', [App\Ecommerce\Http\Controllers\OrderApiController::class, 'cancel'])
        ->name('ecommerce.orders.cancel');

    // Cart (admin view)
    Route::get('ecommerce/cart', [App\Ecommerce\Http\Controllers\CartController::class, 'index'])
        ->name('ecommerce.cart.index');
});

// E-commerce settings and notifications routes (placeholder)
Route::middleware(['auth', 'vertex.permission:admin.access'])->group(function (): void {
    Route::get('ecommerce/settings', function() {
        return view('admin.ecommerce.settings.index');
    })->name('ecommerce.settings');
    
    Route::get('ecommerce/notifications', function() {
        return view('admin.ecommerce.notifications.index');
    })->name('ecommerce.notifications');
});

// Settings routes
Route::middleware(['auth', 'vertex.permission:settings.view'])->group(function (): void {
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
});
