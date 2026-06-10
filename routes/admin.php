<?php

use App\Admin\Http\Controllers\DashboardController;
use App\Auth\Http\Controllers\AdminAuthController;
use App\Content\Http\Controllers\CustomFieldGroupController;
use App\Core\Http\Middleware\SetAdminLocale;
use App\Media\Http\Controllers\MediaController;
use App\Security\Login\Http\Controllers\TwoFactorController;
use App\Admin\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::name("admin.")->prefix("admin")->group(function (): void {
     Route::middleware("guest")->group(function (): void {
         Route::get("/login", [AdminAuthController::class, "showLogin"])->name("login");
         Route::post("/login", [AdminAuthController::class, "login"])->middleware("throttle:5,15");
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

        // Settings & Locale
        Route::get('settings', [SettingsController::class, 'edit'])
            ->middleware('vertex.permission:settings.view')
            ->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])
            ->middleware('vertex.permission:settings.edit')
            ->name('settings.update');
        Route::get('locale/{locale}', [SettingsController::class, 'changeLocale'])
            ->name('locale.change');

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
        if (file_exists(base_path('modules/vertex-forms/routes/admin.php'))) {
            require base_path('modules/vertex-forms/routes/admin.php');
        }
    });
});
