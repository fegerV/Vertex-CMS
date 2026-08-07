<?php

namespace Vertex\Forms\Services;

use Illuminate\Support\Str;
use Vertex\Forms\Jobs\DeliverFormWebhook;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;
use Vertex\Forms\Models\FormWebhookDelivery;

class FormIntegrationService
{
    public function dispatchWebhooks(Form $form, FormSubmission $submission): int
    {
        $webhooks = collect($form->settings['webhooks'] ?? [])
            ->filter(fn ($webhook) => ($webhook['enabled'] ?? true) && filter_var($webhook['url'] ?? null, FILTER_VALIDATE_URL));
        $payload = $this->submissionPayload($form, $submission);

        foreach ($webhooks as $webhook) {
            $delivery = FormWebhookDelivery::query()->create([
                'form_id' => $form->id,
                'submission_id' => $submission->id,
                'name' => $webhook['name'] ?? 'Webhook',
                'url' => $webhook['url'],
                'secret' => $webhook['secret'] ?? Str::random(32),
                'headers' => $webhook['headers'] ?? [],
                'payload' => $payload,
            ]);

            // FormService invokes integrations only after its submission
            // transaction commits, so the delivery is safe to queue directly.
            DeliverFormWebhook::dispatch($delivery);
        }

        return $webhooks->count();
    }

    private function submissionPayload(Form $form, FormSubmission $submission): array
    {
        return [
            'form' => ['id' => $form->id, 'name' => $form->name, 'slug' => $form->slug],
            'submission' => [
                'id' => $submission->submission_id,
                'created_at' => $submission->created_at?->toISOString(),
                'values' => $submission->values->mapWithKeys(
                    fn ($value) => [$value->field->name => $value->value]
                )->all(),
            ],
        ];
    }
}
