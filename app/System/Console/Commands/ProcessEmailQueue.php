<?php

namespace App\System\Console\Commands;

use App\Models\EmailLog;
use App\Models\EmailQueue;
use App\Models\EmailTemplate;
use App\System\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEmailQueue extends Command
{
    protected $signature = 'email:queue {--limit=50 : Maximum emails to process} {--tries=3 : Max retry attempts} {--force : Run without interactive confirmation}';

    protected $description = 'Process pending emails from queue';

    public function handle(EmailService $emailService): int
    {
        $limit = $this->option('limit');
        $maxTries = $this->option('tries');

        if (! $this->option('force') && ! $this->confirm('This will process pending emails. Continue?')) {
            return self::SUCCESS;
        }

        $pending = EmailQueue::query()
            ->where('status', 'pending')
            ->orderBy('priority', 'desc')
            ->limit($limit)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending emails in queue.');

            return self::SUCCESS;
        }

        $this->info('Processing '.$pending->count().' emails...');

        $bar = $this->output->createProgressBar($pending->count());
        $bar->start();

        $processed = 0;
        foreach ($pending as $queueItem) {
            $bar->advance();

            if ($queueItem->retry_count >= $maxTries) {
                $queueItem->status = 'failed';
                $queueItem->last_error = 'Max retries exceeded';
                $queueItem->save();

                continue;
            }

            // Find associated log or create new pending log
            $recipients = $queueItem->recipients ?? [];
            foreach ($recipients as $recipient) {
                try {
                    [$htmlBody, $textBody] = $this->renderBodies($emailService, $queueItem);
                    $log = $this->makeLogFromQueue($queueItem, $recipient);
                    $log->forceFill(['body_text' => $textBody])->save();
                    $emailService->dispatch(
                        $log,
                        $htmlBody,
                        $textBody,
                        [],
                        config_value('mail.from_address'),
                        config_value('mail.from_name'),
                        config_value('mail.reply_to_address'),
                        config_value('mail.reply_to_name')
                    );

                    $processed++;
                } catch (\Throwable $e) {
                    Log::error('Queue email dispatch failed', [
                        'queue_id' => $queueItem->id,
                        'error' => $e->getMessage(),
                    ]);
                    $queueItem->retry_count++;
                    $queueItem->last_error = $e->getMessage();
                    $queueItem->save();
                }
            }

            $queueItem->status = 'processing';
            $queueItem->processed_at = now();
            $queueItem->save();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Processed {$processed} emails successfully.");

        return self::SUCCESS;
    }

    private function renderBodies(EmailService $emailService, EmailQueue $queue): array
    {
        $template = EmailTemplate::query()->where('key', $queue->template_key)->first();
        $variables = $queue->variables ?? [];
        $html = $emailService->renderBody($queue->body_override ?: ($template?->body_html ?? ''), $variables);
        $text = $template?->body_text
            ? $emailService->renderBody($template->body_text, $variables)
            : trim(html_entity_decode(strip_tags(preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return [$html, $text];
    }

    private function makeLogFromQueue(EmailQueue $queue, array $recipient): EmailLog
    {
        return EmailLog::query()->create([
            'template_key' => $queue->template_key,
            'recipient_email' => $recipient['email'],
            'recipient_name' => $recipient['name'] ?? null,
            'subject' => $queue->subject_override ?? '',
            'body_text' => null,
            'headers' => ['X-Queued' => true],
            'template_vars' => $queue->variables,
            'status' => 'pending',
        ]);
    }
}
