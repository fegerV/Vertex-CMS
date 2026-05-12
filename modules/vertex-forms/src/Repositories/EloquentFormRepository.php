<?php

namespace Vertex\Forms\Repositories;

use Vertex\Forms\Contracts\FormRepository;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;
use Illuminate\Support\Facades\DB;

class EloquentFormRepository implements FormRepository
{
    public function find(int $id): ?Form
    {
        return Form::with(['fields'])->find($id);
    }

    public function findBySlug(string $slug): ?Form
    {
        return Form::with(['fields'])->where('slug', $slug)->first();
    }

    public function save(array $data, ?Form $form = null): Form
    {
        if ($form) {
            $form->update($data);
        } else {
            $form = Form::create($data);
        }

        // Handle fields if provided
        if (isset($data['fields'])) {
            $this->syncFields($form, $data['fields']);
        }

        return $form->fresh(['fields']);
    }

    public function delete(Form $form): bool
    {
        return $form->delete();
    }

    public function getActiveForms()
    {
        return Form::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('available_from')
                  ->orWhere('available_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('available_to')
                  ->orWhere('available_to', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSubmissionStats(int $formId, ?string $from = null, ?string $to = null): array
    {
        $query = FormSubmission::where('form_id', $formId);

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $total = $query->count();

        $daily = FormSubmission::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('form_id', $formId)
        ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
        ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date')
        ->get()
        ->keyBy('date')
        ->map(fn($row) => $row->count)
        ->toArray();

        return [
            'total' => $total,
            'daily' => $daily,
        ];
    }

    private function syncFields(Form $form, array $fields): void
    {
        // Clear existing fields if full replacement
        if (isset($fields['_replace'])) {
            $form->fields()->delete();
        }

        foreach ($fields as $index => $fieldData) {
            if (isset($fieldData['id'])) {
                // Update existing
                $field = $form->fields()->find($fieldData['id']);
                if ($field) {
                    $field->update(array_merge($fieldData, ['sort_order' => $index]));
                }
            } else {
                // Create new
                $form->fields()->create(array_merge($fieldData, ['sort_order' => $index]));
            }
        }
    }
}
