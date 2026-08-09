<?php

namespace Vertex\Forms\Services;

use Illuminate\Support\Facades\Storage;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;

class FormSubmissionRetentionService
{
    public function cleanup(?Form $form = null): int
    {
        $deleted = 0;
        $forms = $form ? collect([$form]) : Form::query()->get();

        foreach ($forms as $currentForm) {
            $days = (int) ($currentForm->settings['retention_days'] ?? config('forms.submission_retention_days', 365));
            if ($days <= 0) {
                continue;
            }

            $currentForm->submissions()
                ->where('created_at', '<', now()->subDays($days))
                ->with('values.field')
                ->chunkById(100, function ($submissions) use (&$deleted): void {
                    foreach ($submissions as $submission) {
                        $this->deleteSubmission($submission);
                        $deleted++;
                    }
                });
        }

        return $deleted;
    }

    public function anonymizeSubmission(FormSubmission $submission): void
    {
        $submission->loadMissing('values.field');
        foreach ($submission->values as $value) {
            if (in_array($value->field?->type, ['name', 'email', 'tel', 'address', 'file'], true)) {
                $this->deleteFilesFromValue($value->value, $value->field?->type);
                $value->update(['value' => '[anonymized]']);
            }
        }

        $submission->update([
            'ip_address' => null,
            'user_agent' => null,
            'user_id' => null,
            'meta' => array_merge($submission->meta ?? [], ['anonymized_at' => now()->toISOString()]),
        ]);
    }

    public function deleteSubmission(FormSubmission $submission): void
    {
        $submission->loadMissing('values.field');
        foreach ($submission->values as $value) {
            $this->deleteFilesFromValue($value->value, $value->field?->type);
        }
        $submission->delete();
    }

    private function deleteFilesFromValue(mixed $value, ?string $fieldType): void
    {
        if ($fieldType !== 'file' || ! is_array($value)) {
            return;
        }

        $files = isset($value['path']) ? [$value] : $value;
        foreach ($files as $file) {
            if (! is_array($file) || empty($file['path'])) {
                continue;
            }
            $disk = (string) ($file['disk'] ?? config('forms.upload_disk', 'local'));
            if ($disk === config('forms.upload_disk', 'local')) {
                Storage::disk($disk)->delete($file['path']);
            }
        }
    }
}
