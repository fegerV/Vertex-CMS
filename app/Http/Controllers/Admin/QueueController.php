<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * Display queue monitoring page
     */
    public function index()
    {
        return view('admin.system.queues');
    }

    /**
     * Get queue statistics via API
     */
    public function apiStats()
    {
        try {
            // Get queue sizes (requires Redis connection)
            $redis = app('redis.connection');
            
            $queueStats = [];
            $queueNames = ['default', 'high', 'low', 'emails', 'notifications'];
            
            foreach ($queueNames as $queueName) {
                $size = $redis->llen("queues:{$queueName}");
                $queueStats[$queueName] = [
                    'name' => $queueName,
                    'size' => (int)$size,
                    'status' => $size > 100 ? 'warning' : 'ok'
                ];
            }

            // Get failed jobs count
            $failedCount = DB::table('failed_jobs')->count();
            
            // Get recent failed jobs
            $recentFailed = DB::table('failed_jobs')
                ->orderByDesc('failed_at')
                ->limit(10)
                ->get()
                ->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'uuid' => $job->uuid,
                        'connection' => $job->connection,
                        'queue' => $job->queue,
                        'payload' => json_decode($job->payload, true),
                        'exception' => $job->exception,
                        'failed_at' => $job->failed_at
                    ];
                });

            return response()->json([
                'success' => true,
                'queues' => $queueStats,
                'failed_count' => $failedCount,
                'recent_failed' => $recentFailed
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'queues' => [],
                'failed_count' => null,
                'recent_failed' => [],
                'message' => 'Queue backend is unavailable: '.$e->getMessage(),
            ], 503);
        }
    }

    /**
     * Retry failed job
     */
    public function apiRetryFailed($id)
    {
        try {
            Artisan::call('queue:retry', ['id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Задача добавлена в очередь на повторное выполнение'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry all failed jobs
     */
    public function apiRetryAllFailed()
    {
        try {
            Artisan::call('queue:retry', ['all' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Все задачи добавлены в очередь на повторное выполнение'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete failed job
     */
    public function apiDeleteFailed($id)
    {
        try {
            DB::table('failed_jobs')->where('id', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Задача удалена'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear queue
     */
    public function apiClearQueue(Request $request)
    {
        $validated = $request->validate([
            'queue' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_.:-]+$/'],
        ]);
        $queue = $validated['queue'] ?? 'default';

        try {
            $deleted = app('redis.connection')->del("queues:{$queue}");

            return response()->json([
                'success' => true,
                'message' => 'Очередь очищена',
                'queue' => $queue,
                'deleted' => (int) $deleted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get worker status
     */
    public function apiWorkerStatus()
    {
        $workersActive = Cache::remember('queue_workers_active', 60, function () {
            try {
                app('redis.connection')->ping();
                return Cache::has('queue_worker_heartbeat') || Cache::has('illuminate:queue:restart');
            } catch (\Exception $e) {
                return false;
            }
        });

        return response()->json([
            'success' => true,
            'workers' => [
                'active' => $workersActive,
                'count' => $workersActive ? 1 : 0,
                'last_seen' => $workersActive ? now()->toDateTimeString() : null
            ]
        ]);
    }
}
