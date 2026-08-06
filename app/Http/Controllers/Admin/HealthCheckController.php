<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HealthCheckController extends Controller
{
    /**
     * Базовый health check
     */
    public function up()
    {
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Полный health check
     */
    public function health()
    {
        $checks = [];
        $overallStatus = 'healthy';

        // Проверка базы данных
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'failed: ' . $e->getMessage();
            $overallStatus = 'unhealthy';
        }

        // Проверка Redis
        try {
            Redis::ping();
            $checks['redis'] = 'ok';
        } catch (\Exception $e) {
            $checks['redis'] = 'failed: ' . $e->getMessage();
            $overallStatus = 'degraded';
        }

        // Проверка хранилища
        try {
            Storage::disk('local')->put('.healthcheck', 'test');
            Storage::disk('local')->delete('.healthcheck');
            $checks['storage'] = 'ok';
        } catch (\Exception $e) {
            $checks['storage'] = 'failed: ' . $e->getMessage();
            $overallStatus = 'unhealthy';
        }

        // Проверка места на диске
        $freeSpace = disk_free_space(base_path());
        $totalSpace = disk_total_space(base_path());
        $usedPercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;
        
        if ($usedPercent < 90) {
            $checks['disk_space'] = 'ok (' . round($usedPercent, 1) . '% used)';
        } else {
            $checks['disk_space'] = 'warning: ' . round($usedPercent, 1) . '% used';
            if ($overallStatus === 'healthy') {
                $overallStatus = 'degraded';
            }
        }

        $status = match($overallStatus) {
            'healthy' => 200,
            'degraded' => 200,
            'unhealthy' => 503,
            default => 503,
        };

        return response()->json([
            'status' => $overallStatus,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
        ], $status);
    }

    /**
     * Ready check для Kubernetes
     */
    public function ready()
    {
        // Проверяем что приложение готово принимать трафик
        if (!config('app.installed', false) && !file_exists(storage_path('installed.lock'))) {
            return response()->json(['status' => 'not_ready', 'reason' => 'Application not installed'], 503);
        }

        try {
            DB::connection()->getPdo();
            return response()->json(['status' => 'ready'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'not_ready', 'reason' => $e->getMessage()], 503);
        }
    }
}
