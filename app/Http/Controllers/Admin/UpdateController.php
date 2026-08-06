<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class UpdateController extends Controller
{
    protected UpdateService $updateService;

    public function __construct(UpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    /**
     * Страница управления обновлениями
     */
    public function index()
    {
        $updateInfo = $this->updateService->checkForUpdates();
        
        return view('admin.system.updates.index', compact('updateInfo'));
    }

    /**
     * Проверка обновлений (AJAX)
     */
    public function check()
    {
        $updateInfo = $this->updateService->checkForUpdates();
        
        return response()->json($updateInfo);
    }

    /**
     * Загрузка и установка обновления
     */
    public function update(Request $request)
    {
        $request->validate([
            'download_url' => 'required|url',
        ]);

        try {
            // 1. Скачивание
            $packagePath = $this->updateService->downloadUpdate($request->input('download_url'));

            // 2. Применение
            $result = $this->updateService->applyUpdate($packagePath);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'System updated successfully to version ' . $result['version'],
                    'version' => $result['version'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'rolled_back' => $result['rolled_back'] ?? false,
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Принудительная очистка кэша и оптимизация
     */
    public function optimize()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        if (app()->environment('production')) {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
        }

        return back()->with('success', 'System optimized successfully');
    }
}
