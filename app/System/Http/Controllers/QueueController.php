<?php

namespace App\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Jobs\JobInterface;

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
        $connection = Queue::connection('redis');
        $jobs = [];

        for ($i = 0; $i < 50; $i++) {
            $job = $connection->pop($queue);
            if (!$job) {
                break;
            }
            $jobs[] = [
                'id' => $job->getJobId(),
                'name' => $job->resolveName(),
                'payload' => json_decode($job->getRawBody(), true),
            ];
        }

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
