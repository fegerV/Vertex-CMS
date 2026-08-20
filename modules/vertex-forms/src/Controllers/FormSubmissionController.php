<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormSubmission;
use Vertex\Forms\Services\FormExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormSubmissionController extends Controller
{
    public function __construct(
        private readonly FormExportService $exportService,
    ) {}
    /**
     * List submissions for a form.
     */
    public function index(Form $form, Request $request): JsonResponse
    {
        $query = $form->submissions()->with(["values.field", "user:id,name"]);

        // Filters
        if ($request->filled("status")) {
            $query->where("status", $request->input("status"));
        }
        if ($request->filled("date_from")) {
            $query->where("created_at", ">=", $request->input("date_from"));
        }
        if ($request->filled("date_to")) {
            $query->where("created_at", "<=", $request->input("date_to"));
        }
        if ($request->filled("search")) {
            $search = $request->input("search");
            $query->where(function ($q) use ($search) {
                $q->where("submission_id", "like", "%{$search}%")
                  ->orWhere("ip_address", "like", "%{$search}%");
            });
        }

        $submissions = $query->orderBy("created_at", "desc")->paginate($request->integer("per_page", 20));

        return response()->json([
            "submissions" => $submissions->through(fn ($s) => [
                "id" => $s->id,
                "submission_id" => $s->submission_id,
                "ip_address" => $s->ip_address,
                "status" => $s->status,
                "user" => $s->user?->name,
                "created_at" => $s->created_at?->toDateTimeString(),
                "values_count" => $s->values->count(),
                "total" => $s->meta["total"] ?? null,
            ]),
            "pagination" => [
                "total" => $submissions->total(),
                "per_page" => $submissions->perPage(),
                "current_page" => $submissions->currentPage(),
                "last_page" => $submissions->lastPage(),
            ],
        ]);
    }

    /**
     * Show a single submission.
     */
    public function show(Form $form, FormSubmission $submission): JsonResponse
    {
        $submission->load(["values.field", "user"]);

        return response()->json([
            "submission" => [
                "id" => $submission->id,
                "submission_id" => $submission->submission_id,
                "ip_address" => $submission->ip_address,
                "user_agent" => $submission->user_agent,
                "status" => $submission->status,
                "meta" => $submission->meta,
                "created_at" => $submission->created_at?->toDateTimeString(),
                "values" => $submission->values->map(fn ($v) => [
                    "field_name" => $v->field->name,
                    "field_label" => $v->field->label,
                    "value" => $v->value,
                ]),
            ],
        ]);
    }

    /**
     * Delete a submission.
     */
    public function destroy(Form $form, FormSubmission $submission): JsonResponse
    {
        $submission->delete();

        return response()->json(["ok" => true]);
    }

    /**
     * Clear all submissions for a form.
     */
    public function clear(Form $form): JsonResponse
    {
        $form->submissions()->delete();

        return response()->json(["ok" => true, "message" => __("forms.all_submissions_deleted")]);
    }

    /**
     * Export submissions to CSV with pagination.
     */
    public function export(Form $form, Request $request)
    {
        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 100);
        $all = $request->boolean('all', false);
        
        if ($all) {
            // Export all submissions
            $csvContent = $this->exportService->exportAllToCsv($form);
        } else {
            // Export paginated
            $csvContent = $this->exportService->exportToCsv($form, $page, $perPage);
        }
        
        $filename = "form-{$form->slug}-" . now()->format("Y-m-d") . ".csv";
        
        return response($csvContent, 200, [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
        ]);
    }
    
    /**
     * Get paginated submissions data for frontend.
     */
    public function paginated(Form $form, Request $request): JsonResponse
    {
        $page = $request->integer('page', 1);
        $perPage = $request->integer('per_page', 20);
        
        $result = $this->exportService->getPaginatedSubmissions($form, $page, $perPage);
        
        return response()->json([
            'submissions' => $result['data']->map(fn ($s) => [
                'id' => $s->id,
                'submission_id' => $s->submission_id,
                'ip_address' => $s->ip_address,
                'status' => $s->status,
                'user' => $s->user?->name,
                'created_at' => $s->created_at?->toDateTimeString(),
                'values' => $s->values->map(fn ($v) => [
                    'field_name' => $v->field->name,
                    'field_label' => $v->field->label,
                    'value' => $v->value,
                ]),
                'total' => $s->meta['total'] ?? null,
            ]),
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $result['per_page'],
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
            ],
        ]);
    }
}
