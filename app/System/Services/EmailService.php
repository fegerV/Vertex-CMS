<?php

namespace App\System\Services;

use App\Models\EmailLog;
use App\Models\EmailQueue;
use App\Models\EmailTemplate;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailService
{
    public function __construct(
        private readonly ViewFactory $views,
        private readonly Mailer $mailer,
    ) {
    }

    /**
     * Send email by template key with variables
     */
    public function send(string $templateKey, string $recipientEmail, string $recipientName = null, array $variables = [], array $attachments = []): EmailLog
    {
        $template = EmailTemplate::query()->where('key', $templateKey)->where('is_active', true)->first();

        if (!$template) {
            throw new \Exception("Email template '{$templateKey}' not found or inactive.");
        }

        // Render HTML and text using Blade compiler for variables
        $html = Blade::render($template->body_html, $variables);
        $text = $template->body_text ? Blade::render($template->body_text, $variables) : strip_tags($html);

        // Resolve from settings
        $fromEmail = config_value('mail.from_address') ?? config('mail.from.address');
        $fromName = config_value('mail.from_name') ?? config('mail.from.name');
        $replyToEmail = config_value('mail.reply_to_address');
        $replyToName = config_value('mail.reply_to_name');

        // Build headers
        $headers = [
            'X-Email-Template' => $templateKey,
            'X-VercelCMS-Version' => config('vertex.version', '1.0'),
        ];

        // Create log entry
        $log = EmailLog::query()->create([
            'template_key' => $templateKey,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'subject' => $template->subject,
            'body_text' => $text,
            'headers' => $headers,
            'attachments' => $attachments,
            'template_vars' => $variables,
            'status' => 'pending',
        ]);

        // Send immediately or queue
        if (config_value('mail.queue_enabled', false)) {
            $this->queue($log, $variables, $attachments);
        } else {
            $this->dispatch($log, $html, $text, $attachments, $fromEmail, $fromName, $replyToEmail, $replyToName);
        }

        return $log;
    }

    /**
     * Render Blade template string with variables
     */
    public function renderBody(string $template, array $variables = []): string
    {
        try {
            return Blade::render($template, $variables);
        } catch (Throwable $e) {
            Log::error('Email render failed', ['error' => $e->getMessage(), 'template' => $template]);
            return $template; // fallback: return raw
        }
    }

    /**
     * Queue email for async sending
     */
    public function queue(EmailLog $log, array $variables = [], array $attachments = []): EmailQueue
    {
        $queue = EmailQueue::query()->create([
            'template_key' => $log->template_key,
            'recipients' => [[
                'email' => $log->recipient_email,
                'name' => $log->recipient_name,
            ]],
            'variables' => $variables,
            'subject_override' => $log->subject,
            'priority' => 0,
            'retry_count' => 0,
            'status' => 'pending',
        ]);

        return $queue;
    }

    /**
     * Dispatch email (actual send)
     */
    public function dispatch(
        EmailLog $log,
        string $htmlBody,
        string $textBody,
        array $attachments = [],
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): bool {
        $fromEmail ??= config_value('mail.from_address') ?? config('mail.from.address');
        $fromName ??= config_value('mail.from_name') ?? config('mail.from.name');

        try {
            $message = $this->mailer->raw($htmlBody, function ($m) use (
                $log,
                $fromEmail,
                $fromName,
                $replyToEmail,
                $replyToName,
                $attachments
            ) {
                $m->to($log->recipient_email, $log->recipient_name)
                    ->subject($log->subject);

                $m->from($fromEmail, $fromName);

                if ($replyToEmail) {
                    $m->replyTo($replyToEmail, $replyToName);
                }

                foreach ($attachments as $attachment) {
                    if (is_string($attachment)) {
                        $m->attach($attachment);
                    } elseif (isset($attachment['path'])) {
                        $m->attach($attachment['path'], $attachment['options'] ?? []);
                    }
                }

                // Custom headers
                foreach ($log->headers ?? [] as $key => $value) {
                    $m->getHeaders()->addTextHeader($key, $value);
                }
            });

            $log->status = 'sent';
            $log->sent_at = now();
            $log->save();

            return true;
        } catch (Throwable $e) {
            Log::error('Email send failed', [
                'template' => $log->template_key,
                'to' => $log->recipient_email,
                'error' => $e->getMessage(),
            ]);

            $log->status = 'failed';
            $log->error_message = $e->getMessage();
            $log->failed_at = now();
            $log->save();

            return false;
        }
    }

    /**
     * Test SMTP connection
     */
    public function testConnection(string $testEmail): array
    {
        try {
            $this->mailer->send([], [], function ($m) use ($testEmail) {
                $m->to($testEmail)
                    ->subject('Test email from VertexCMS')
                    ->text('This is a test email. If you received it, SMTP is working.');
            });

            return ['success' => true, 'message' => 'Test email sent successfully'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
