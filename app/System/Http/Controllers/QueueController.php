<?php

namespace App\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class QueueController extends Controller
{
    public function index(): View
    {
        $queues = [
            'default' => Queue::connection()->size(),
            'emails' => Queue::connection('redis')->size('emails'),
            'imports' => Queue::connection('redis')->size('imports'),
            'exports' => Queue::connection('redis')->size('exports'),
        ];

        $failedJobs = Queue::failed();

        return view('admin.system.queues', compact('queues', 'failedJobs'));
    }

    public function show(string $queue): View
    {
        $jobs = collect(Redis::connection()->lrange("queues:{$queue}", 0, 49))
            ->map(function (string $rawPayload): array {
                $payload = json_decode($rawPayload, true) ?: [];

                return [
                    'id' => $payload['uuid'] ?? $payload['id'] ?? null,
                    'name' => $payload['displayName'] ?? $payload['job'] ?? 'Unknown job',
                    'payload' => $payload,
                ];
            })
            ->all();

        return view('admin.system.queue-jobs', compact('queue', 'jobs'));
    }

    public function retryFailed(int $id)
    {
        $failedJob = Queue::failed()->firstWhere('id', $id);
        
        if ($failedJob) {
            Queue::connection($failedJob->connection)->push(
                $failedJob->payload['data']['command']
            );
            
            $failedJob->delete();
        }

        return redirect()->route('admin.system.queues')
            ->with('status', 'Задача добавлена в очередь.');
    }

    public function deleteFailed(int $id)
    {
        $failedJob = Queue::failed()->firstWhere('id', $id);
        
        if ($failedJob) {
            $failedJob->delete();
        }

        return redirect()->route('admin.system.queues')
            ->with('status', 'Неудачная задача удалена.');
    }

    public function flushFailed()
    {
        foreach (Queue::failed() as $failedJob) {
            $failedJob->delete();
        }

        return redirect()->route('admin.system.queues')
            ->with('status', 'Все неудачные задачи удалены.');
    }
}
