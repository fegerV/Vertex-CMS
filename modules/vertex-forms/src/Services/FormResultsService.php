<?php

namespace Vertex\Forms\Services;

use Illuminate\Support\Facades\DB;
use Vertex\Forms\Models\Form;

class FormResultsService
{
    public function poll(Form $form): array
    {
        abort_unless($form->type === 'poll' && ($form->settings['show_results'] ?? false), 404);
        $total = $form->submissions()->whereNotIn('status', ['draft', 'spam', 'trashed'])->count();
        $fields = [];

        foreach ($form->fields()->whereIn('type', ['radio', 'select', 'checkbox_group'])->get() as $field) {
            $counts = DB::table('form_submission_values')
                ->join('form_submissions', 'form_submissions.id', '=', 'form_submission_values.submission_id')
                ->where('form_submissions.form_id', $form->id)
                ->where('form_submission_values.field_id', $field->id)
                ->whereNotIn('form_submissions.status', ['draft', 'spam', 'trashed'])
                ->select('form_submission_values.value', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('form_submission_values.value')
                ->get()
                ->mapWithKeys(function ($row): array {
                    $decoded = json_decode($row->value, true);
                    $answer = is_scalar($decoded) ? (string) $decoded : $row->value;

                    return [$answer => (int) $row->aggregate];
                })->all();
            $fields[$field->name] = ['label' => $field->label, 'answers' => $counts];
        }

        return ['total_responses' => $total, 'fields' => $fields];
    }
}
