<?php

use App\Admin\Http\Controllers\DashboardController;
use App\Admin\Http\Controllers\RoleController;
use App\Admin\Http\Controllers\SettingsController;
use App\Admin\Http\Controllers\UserController;
use App\Auth\Http\Controllers\AdminAuthController;
use App\Builder\Http\Controllers\AdvancedBuilderController;
use App\Builder\Http\Controllers\PageBuilderController;
use App\Content\Http\Controllers\CustomFieldGroupController;
use App\Content\Http\Controllers\PageController;
use App\Media\Http\Controllers\MediaController;
use App\Seo\Http\Controllers\RedirectController;
use App\System\Http\Controllers\SystemController;
use App\Taxonomy\Http\Controllers\AdminTaxonomyController;
use App\Taxonomy\Http\Controllers\AdminTermController;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->prefix('admin')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,15');
    });

    Route::middleware(['auth', 'vertex.permission:admin.access'])->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('users', [UserController::class, 'index'])
            ->middleware('vertex.permission:users.view')
            ->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])
            ->middleware('vertex.permission:users.create')
            ->name('users.create');
        Route::post('users', [UserController::class, 'store'])
            ->middleware('vertex.permission:users.create')
            ->name('users.store');
        Route::get('users/{user}/edit', [UserController::class, 'edit'])
            ->middleware('vertex.permission:users.edit')
            ->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])
            ->middleware('vertex.permission:users.edit')
            ->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])
            ->middleware('vertex.permission:users.delete')
            ->name('users.destroy');

        Route::get('roles', [RoleController::class, 'index'])
            ->middleware('vertex.permission:roles.view')
            ->name('roles.index');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
            ->middleware('vertex.permission:roles.edit')
            ->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])
            ->middleware('vertex.permission:roles.edit')
            ->name('roles.update');

        Route::get('settings', [SettingsController::class, 'edit'])
            ->middleware('vertex.permission:settings.view')
            ->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])
            ->middleware('vertex.permission:settings.edit')
            ->name('settings.update');

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
        Route::get('pages/builder/presets', [AdvancedBuilderController::class, 'getSharedPresets'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.presets.index');
        Route::post('pages/builder/presets', [AdvancedBuilderController::class, 'storeSharedPreset'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.presets.store');
        Route::put('pages/builder/presets/{presetId}', [AdvancedBuilderController::class, 'updateSharedPreset'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.presets.update');
        Route::delete('pages/builder/presets/{presetId}', [AdvancedBuilderController::class, 'destroySharedPreset'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.presets.destroy');
        Route::get('pages/builder/shared-templates', [AdvancedBuilderController::class, 'getSharedTemplates'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.shared-templates.index');
        Route::post('pages/builder/shared-templates', [AdvancedBuilderController::class, 'storeSharedTemplate'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.shared-templates.store');
        Route::put('pages/builder/shared-templates/{templateId}', [AdvancedBuilderController::class, 'updateSharedTemplate'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.shared-templates.update');
        Route::delete('pages/builder/shared-templates/{templateId}', [AdvancedBuilderController::class, 'destroySharedTemplate'])
            ->middleware('vertex.permission:pages.edit')
            ->name('pages.builder.shared-templates.destroy');
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

        Route::get('taxonomies', [AdminTaxonomyController::class, 'index'])
            ->middleware('vertex.permission:taxonomy.view')
            ->name('taxonomies.index');
        Route::get('taxonomies/create', [AdminTaxonomyController::class, 'create'])
            ->middleware('vertex.permission:taxonomy.create')
            ->name('taxonomies.create');
        Route::post('taxonomies', [AdminTaxonomyController::class, 'store'])
            ->middleware('vertex.permission:taxonomy.create')
            ->name('taxonomies.store');
        Route::get('taxonomies/{taxonomy}/edit', [AdminTaxonomyController::class, 'edit'])
            ->middleware('vertex.permission:taxonomy.edit')
            ->name('taxonomies.edit');
        Route::put('taxonomies/{taxonomy}', [AdminTaxonomyController::class, 'update'])
            ->middleware('vertex.permission:taxonomy.edit')
            ->name('taxonomies.update');
        Route::delete('taxonomies/{taxonomy}', [AdminTaxonomyController::class, 'destroy'])
            ->middleware('vertex.permission:taxonomy.delete')
            ->name('taxonomies.destroy');

        Route::get('taxonomies/{taxonomy}/terms/create', [AdminTermController::class, 'create'])
            ->middleware('vertex.permission:taxonomy.create')
            ->name('taxonomies.terms.create');
        Route::post('taxonomies/{taxonomy}/terms', [AdminTermController::class, 'store'])
            ->middleware('vertex.permission:taxonomy.create')
            ->name('taxonomies.terms.store');
        Route::get('taxonomies/{taxonomy}/terms/{term}/edit', [AdminTermController::class, 'edit'])
            ->middleware('vertex.permission:taxonomy.edit')
            ->name('taxonomies.terms.edit');
        Route::put('taxonomies/{taxonomy}/terms/{term}', [AdminTermController::class, 'update'])
            ->middleware('vertex.permission:taxonomy.edit')
            ->name('taxonomies.terms.update');
        Route::delete('taxonomies/{taxonomy}/terms/{term}', [AdminTermController::class, 'destroy'])
            ->middleware('vertex.permission:taxonomy.delete')
            ->name('taxonomies.terms.destroy');

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
    });
});
