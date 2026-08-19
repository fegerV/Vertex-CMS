<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;
    public int $tries = 3;

    public function __construct(
        public string $to,
        public string $subject,
        public string $view,
        public array $data = [],
    ) {}

    public function handle(): void
    {
        Mail::send($this->view, $this->data, function ($message) {
            $message->to($this->to)->subject($this->subject);
        });
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error("Failed to send email to {$this->to}: " . $exception->getMessage());
    }
}
