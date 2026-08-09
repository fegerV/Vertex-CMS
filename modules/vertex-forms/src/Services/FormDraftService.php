<?php

namespace Vertex\Forms\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Vertex\Forms\Events\FormDraftSaved;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;

class FormDraftService
{
    public function save(Form $form, Request $request): array
    {
        abort_unless($form->settings['save_resume_enabled'] ?? false, 404);
        $token = trim((string) $request->input('resume_token', '')) ?: Str::random(64);
        $tokenHash = hash('sha256', $token);
        $submission = FormSubmission::query()
            ->where('form_id', $form->id)
            ->where('resume_token_hash', $tokenHash)
            ->first();

        if ($submission?->resume_expires_at?->isPast()) {
            throw new GoneHttpException(__('forms.error_draft_expired'));
        }

        $allowedFields = $form->fields()->where('visible', true)->get()->keyBy('name');
        $values = collect($request->except(['_token', 'resume_token']))
            ->filter(fn ($value, $name) => $allowedFields->has($name) && ! $request->hasFile($name));
        $expiresAt = now()->addDays(max(1, (int) ($form->settings['resume_days'] ?? 30)));

        $submission = DB::transaction(function () use ($form, $request, $submission, $tokenHash, $expiresAt, $values, $allowedFields) {
            $submission ??= FormSubmission::create([
                'form_id' => $form->id,
                'submission_id' => Str::uuid()->toString(),
                'resume_token_hash' => $tokenHash,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
                'status' => 'draft',
                'resume_expires_at' => $expiresAt,
            ]);
            $submission->update(['resume_expires_at' => $expiresAt]);

            foreach ($values as $name => $value) {
                $field = $allowedFields->get($name);
                $submission->values()->updateOrCreate(
                    ['field_id' => $field->id],
                    ['value' => $value]
                );
            }

            return $submission->load('values.field');
        });
        FormDraftSaved::dispatch($form, $submission);

        return [
            'resume_token' => $token,
            'expires_at' => $submission->resume_expires_at?->toISOString(),
            'values' => $submission->values->mapWithKeys(fn ($value) => [$value->field->name => $value->value])->all(),
        ];
    }

    public function load(Form $form, string $token): array
    {
        abort_unless($form->settings['save_resume_enabled'] ?? false, 404);
        $submission = FormSubmission::query()
            ->where('form_id', $form->id)
            ->where('resume_token_hash', hash('sha256', $token))
            ->with('values.field')
            ->firstOrFail();

        if ($submission->resume_expires_at?->isPast()) {
            throw new GoneHttpException(__('forms.error_draft_expired'));
        }

        return [
            'expires_at' => $submission->resume_expires_at?->toISOString(),
            'values' => $submission->values->mapWithKeys(fn ($value) => [$value->field->name => $value->value])->all(),
        ];
    }

    public function consume(Form $form, ?string $token): void
    {
        if (! $token) {
            return;
        }

        FormSubmission::query()
            ->where('form_id', $form->id)
            ->where('resume_token_hash', hash('sha256', $token))
            ->where('status', 'draft')
            ->delete();
    }
}
