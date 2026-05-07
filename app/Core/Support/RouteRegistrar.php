<?php

namespace App\Core\Support;

use Illuminate\Support\Facades\Route;

class RouteRegistrar
{
    public function register(): void
    {
        Route::middleware('web')->group(base_path('routes/install.php'));
        Route::middleware('web')->group(base_path('routes/admin.php'));
        Route::middleware('web')->group(base_path('routes/web.php'));
        Route::prefix('admin/api')->middleware(['web', 'auth'])->group(base_path('routes/api.php'));
    }
}

