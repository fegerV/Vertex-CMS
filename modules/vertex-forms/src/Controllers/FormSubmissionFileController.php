<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;
use Vertex\Forms\Models\FormSubmissionValue;

class FormSubmissionFileController extends Controller
{
    public function download(
        Form $form,
        FormSubmission $submission,
        FormSubmissionValue $value,
        int $fileIndex = 0,
    ): StreamedResponse {
        abort_unless($submission->form_id === $form->id, 404);
        abort_unless($value->submission_id === $submission->id, 404);
        abort_unless($value->field?->type === 'file', 404);

        $files = $value->value;
        $file = isset($files['path']) ? $files : ($files[$fileIndex] ?? null);
        abort_unless(is_array($file), 404);

        $disk = (string) ($file['disk'] ?? config('forms.upload_disk', 'local'));
        $path = (string) ($file['path'] ?? '');
        abort_unless($disk === config('forms.upload_disk', 'local') && $path !== '', 404);
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->download(
            $path,
            basename((string) ($file['name'] ?? basename($path))),
            ['Content-Type' => (string) ($file['mime'] ?? 'application/octet-stream')]
        );
    }
}
