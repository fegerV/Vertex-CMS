<?php

namespace Vertex\Forms\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;

class FormExportService
{
    /**
     * Export form submissions to CSV with pagination support.
     */
    public function exportToCsv(Form $form, int $page = 1, int $perPage = 100): string
    {
        $submissions = $this->getSubmissionsQuery($form)
            ->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $fields = $form->fields()->orderBy('sort_order')->get();
        
        // Build CSV header
        $headers = [
            'ID',
            'Submission ID',
            'Submitted At',
            'Status',
            'User ID',
            'IP Address',
        ];
        
        foreach ($fields as $field) {
            $headers[] = $field->label ?: $field->name;
        }
        
        $headers[] = 'Total';
        
        // Build CSV rows
        $rows = [];
        foreach ($submissions as $submission) {
            $row = [
                $submission->id,
                $submission->submission_id,
                $submission->created_at->toDateTimeString(),
                $submission->status,
                $submission->user_id ?? '',
                $submission->ip_address ?? '',
            ];
            
            // Add field values
            $valuesByFieldId = $submission->values->keyBy('field_id');
            foreach ($fields as $field) {
                $valueObj = $valuesByFieldId->get($field->id);
                $value = $valueObj?->value;
                
                // Format value for CSV
                if (is_array($value)) {
                    if (isset($value['disk']) && isset($value['path'])) {
                        // File field
                        $value = config('app.url') . '/storage/' . ltrim($value['path'], '/');
                    } else {
                        // Array field (checkboxes, etc.)
                        $value = implode('; ', $value);
                    }
                } elseif ($value === null) {
                    $value = '';
                }
                
                $row[] = $value;
            }
            
            // Add total
            $row[] = $submission->meta['total'] ?? '';
            
            $rows[] = $row;
        }
        
        return $this->generateCsvString($headers, $rows);
    }
    
    /**
     * Get total count of submissions for pagination.
     */
    public function getTotalSubmissionsCount(Form $form): int
    {
        return $this->getSubmissionsQuery($form)->count();
    }
    
    /**
     * Get paginated submissions data.
     */
    public function getPaginatedSubmissions(Form $form, int $page = 1, int $perPage = 20): array
    {
        $query = $this->getSubmissionsQuery($form);
        $total = $query->count();
        
        $submissions = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->with(['values.field', 'user'])
            ->get();
        
        return [
            'data' => $submissions,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }
    
    /**
     * Export all submissions to CSV file (no pagination).
     */
    public function exportAllToCsv(Form $form): string
    {
        $submissions = $this->getSubmissionsQuery($form)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $fields = $form->fields()->orderBy('sort_order')->get();
        
        // Build CSV header
        $headers = [
            'ID',
            'Submission ID',
            'Submitted At',
            'Status',
            'User ID',
            'IP Address',
        ];
        
        foreach ($fields as $field) {
            $headers[] = $field->label ?: $field->name;
        }
        
        $headers[] = 'Total';
        
        // Build CSV rows
        $rows = [];
        foreach ($submissions as $submission) {
            $row = [
                $submission->id,
                $submission->submission_id,
                $submission->created_at->toDateTimeString(),
                $submission->status,
                $submission->user_id ?? '',
                $submission->ip_address ?? '',
            ];
            
            $valuesByFieldId = $submission->values->keyBy('field_id');
            foreach ($fields as $field) {
                $valueObj = $valuesByFieldId->get($field->id);
                $value = $valueObj?->value;
                
                if (is_array($value)) {
                    if (isset($value['disk']) && isset($value['path'])) {
                        $value = config('app.url') . '/storage/' . ltrim($value['path'], '/');
                    } else {
                        $value = implode('; ', $value);
                    }
                } elseif ($value === null) {
                    $value = '';
                }
                
                $row[] = $value;
            }
            
            $row[] = $submission->meta['total'] ?? '';
            $rows[] = $row;
        }
        
        return $this->generateCsvString($headers, $rows);
    }
    
    /**
     * Get base query for form submissions.
     */
    private function getSubmissionsQuery(Form $form): Builder
    {
        return FormSubmission::where('form_id', $form->id)
            ->with(['values']);
    }
    
    /**
     * Generate CSV string from headers and rows.
     */
    private function generateCsvString(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add BOM for Excel UTF-8 compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write header
        fputcsv($output, $headers);
        
        // Write rows
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }
}
