<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;
use Vertex\Forms\Services\FormSubmissionRetentionService;

class FormSubmissionController extends Controller
{
    private const STATUSES = ['unread', 'read', 'completed', 'spam', 'trashed'];

    public function __construct(private readonly FormSubmissionRetentionService $retentionService) {}

    public function index(Form $form, Request $request): JsonResponse
    {
        $this->validateFilters($request);
        $submissions = $this->filteredQuery($form, $request)
            ->with(['values.field', 'user:id,name'])
            ->latest()
            ->paginate(min(100, max(1, $request->integer('per_page', 20))));

        return response()->json([
            'submissions' => $submissions->through(fn ($submission) => [
                'id' => $submission->id,
                'submission_id' => $submission->submission_id,
                'ip_address' => $submission->ip_address,
                'status' => $submission->status,
                'user' => $submission->user?->name,
                'created_at' => $submission->created_at?->toDateTimeString(),
                'values_count' => $submission->values->count(),
                'total' => $submission->meta['total'] ?? null,
            ]),
            'pagination' => [
                'total' => $submissions->total(),
                'per_page' => $submissions->perPage(),
                'current_page' => $submissions->currentPage(),
                'last_page' => $submissions->lastPage(),
            ],
        ]);
    }

    public function show(Form $form, FormSubmission $submission): JsonResponse
    {
        $this->assertBelongsToForm($form, $submission);
        if ($submission->status === 'unread') {
            $submission->update(['status' => 'read']);
        }
        $submission->load(['values.field', 'user']);

        return response()->json(['submission' => [
            'id' => $submission->id,
            'submission_id' => $submission->submission_id,
            'ip_address' => $submission->ip_address,
            'user_agent' => $submission->user_agent,
            'status' => $submission->status,
            'meta' => $submission->meta,
            'created_at' => $submission->created_at?->toDateTimeString(),
            'values' => $submission->values->map(fn ($value) => [
                'field_name' => $value->field->name,
                'field_label' => $value->field->label,
                'value' => $value->value,
            ]),
        ]]);
    }

    public function updateStatus(Form $form, FormSubmission $submission, Request $request): JsonResponse
    {
        $this->assertBelongsToForm($form, $submission);
        $validated = $request->validate(['status' => ['required', 'in:'.implode(',', self::STATUSES)]]);
        $submission->update($validated);

        return response()->json(['ok' => true, 'status' => $submission->status]);
    }

    public function bulk(Form $form, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:500'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:mark_read,mark_unread,mark_spam,trash,delete'],
        ]);
        $submissions = $form->submissions()->whereKey($validated['ids'])->get();

        if ($validated['action'] === 'delete') {
            $submissions->each(fn ($submission) => $this->retentionService->deleteSubmission($submission));
        } else {
            $status = match ($validated['action']) {
                'mark_read' => 'read',
                'mark_unread' => 'unread',
                'mark_spam' => 'spam',
                'trash' => 'trashed',
            };
            $form->submissions()->whereKey($submissions->modelKeys())->update(['status' => $status]);
        }

        return response()->json(['ok' => true, 'affected' => $submissions->count()]);
    }

    public function destroy(Form $form, FormSubmission $submission): JsonResponse
    {
        $this->assertBelongsToForm($form, $submission);
        $this->retentionService->deleteSubmission($submission);

        return response()->json(['ok' => true]);
    }

    public function anonymize(Form $form, FormSubmission $submission): JsonResponse
    {
        $this->assertBelongsToForm($form, $submission);
        $this->retentionService->anonymizeSubmission($submission);

        return response()->json(['ok' => true]);
    }

    public function clear(Form $form): JsonResponse
    {
        $form->submissions()->with('values.field')->chunkById(100, function ($submissions): void {
            $submissions->each(fn ($submission) => $this->retentionService->deleteSubmission($submission));
        });

        return response()->json(['ok' => true, 'message' => __('forms.all_submissions_deleted')]);
    }

    public function export(Form $form, Request $request): StreamedResponse
    {
        $this->validateFilters($request);
        $fields = $form->fields()->get();
        $filename = "form-{$form->slug}-".now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($form, $request, $fields): void {
            $stream = fopen('php://output', 'wb');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, array_merge(['ID', 'Date', 'IP', 'User ID', 'Status'], $fields->pluck('label')->all()));

            $this->filteredQuery($form, $request)
                ->with('values')
                ->orderBy('id')
                ->chunkById(200, function ($submissions) use ($stream, $fields): void {
                    foreach ($submissions as $submission) {
                        $values = $submission->values->keyBy('field_id');
                        $row = [
                            $submission->id,
                            $submission->created_at?->format('Y-m-d H:i:s'),
                            $submission->ip_address,
                            $submission->user_id,
                            $submission->status,
                        ];
                        foreach ($fields as $field) {
                            $row[] = $this->csvValue($values->get($field->id)?->value);
                        }
                        fputcsv($stream, $row);
                    }
                });
            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filteredQuery(Form $form, Request $request): Builder
    {
        return FormSubmission::query()
            ->where('form_id', $form->id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('date_from'), fn ($query) => $query->where('created_at', '>=', $request->date('date_from')->startOfDay()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('created_at', '<=', $request->date('date_to')->endOfDay()))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim().'%';
                $query->where(function ($nested) use ($search): void {
                    $nested->where('submission_id', 'like', $search)
                        ->orWhere('ip_address', 'like', $search)
                        ->orWhereHas('values', fn ($values) => $values->where('value', 'like', $search));
                });
            });
    }

    private function validateFilters(Request $request): void
    {
        $request->validate([
            'status' => ['nullable', 'in:'.implode(',', self::STATUSES)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function csvValue(mixed $value): string
    {
        $value = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) ($value ?? '');

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }

    private function assertBelongsToForm(Form $form, FormSubmission $submission): void
    {
        abort_unless($submission->form_id === $form->id, 404);
    }
}
