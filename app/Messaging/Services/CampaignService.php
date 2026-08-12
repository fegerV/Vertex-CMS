<?php

namespace App\Messaging\Services;

use App\System\Services\EmailService;

class CampaignService
{
    public function __construct(private readonly EmailService $email) {}

    public function queue(string $template, array $subscribers, array $variables = []): array
    {
        $queued = [];
        $deduplicated = [];
        foreach ($subscribers as $subscriber) {
            $email = strtolower(trim((string) ($subscriber['email'] ?? '')));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL) || isset($deduplicated[$email]) || ! ($subscriber['consented'] ?? false)) {
                continue;
            }
            $deduplicated[$email] = true;
            $queued[] = $this->email->send($template, $email, $subscriber['name'] ?? null, $variables + ['subscriber' => $subscriber]);
        }

        return $queued;
    }
}
