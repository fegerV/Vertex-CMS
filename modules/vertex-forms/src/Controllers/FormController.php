<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Services\FormService;
use Vertex\Forms\Services\FormImportExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FormController extends Controller
{
    public function __construct(
        private readonly FormService $formService,
        private readonly FormImportExportService $importExport,
    ) {}

    public function index(): View
    {
        $forms = Form::query()->orderBy("sort_order")->orderBy("created_at", "desc")->get();

        $stats = [];
        foreach ($forms as $form) {
            $stats[$form->id] = $this->formService->getStats($form);
        }

        return view("forms::admin.forms.index", [
            "forms" => $forms,
            "stats" => $stats,
        ]);
    }

    public function create(): View
    {
        return view("forms::admin.forms.builder", ["form" => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "slug" => ["required", "string", "max:100", "alpha_dash", "unique:forms,slug"],
            "type" => ["required", "string", "in:standard,calculator,survey,poll"],
            "description" => ["nullable", "string", "max:2000"],
            "settings" => ["nullable", "array"],
            "require_login" => ["nullable", "boolean"],
            "entry_limit" => ["nullable", "integer", "min:0"],
            "daily_limit" => ["nullable", "integer", "min:0"],
            "available_from" => ["nullable", "date"],
            "available_to" => ["nullable", "date", "after_or_equal:available_from"],
        ]);

        Form::create([
            ...$validated,
            "created_by" => $request->user()?->id,
            "settings" => $validated["settings"] ?? [],
        ]);

        return redirect()->route("admin.forms.index")->with("status", __("forms.created"));
    }

    public function edit(Form $form): View
    {
        return view("forms::admin.forms.builder", ["form" => $form->load("fields")]);
    }

    public function update(Request $request, Form $form): RedirectResponse
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "slug" => ["required", "string", "max:100", "alpha_dash", "unique:forms,slug," . $form->id],
            "type" => ["required", "string", "in:standard,calculator,survey,poll"],
            "description" => ["nullable", "string", "max:2000"],
            "settings" => ["nullable", "array"],
            "require_login" => ["nullable", "boolean"],
            "entry_limit" => ["nullable", "integer", "min:0"],
            "daily_limit" => ["nullable", "integer", "min:0"],
            "available_from" => ["nullable", "date"],
            "available_to" => ["nullable", "date", "after_or_equal:available_from"],
            "is_active" => ["nullable", "boolean"],
            "sort_order" => ["nullable", "integer"],
        ]);

        $form->update($validated);

        return redirect()->route("admin.forms.index")->with("status", __("forms.updated"));
    }

    public function destroy(Form $form): RedirectResponse
    {
        $form->delete();
        return redirect()->route("admin.forms.index")->with("status", __("forms.deleted"));
    }

    public function preview(Form $form): View
    {
        // Use builder in preview mode (read-only - future enhancement)
        return view("forms::admin.forms.builder", [
            "form" => $form->load("fields"),
            "preview_mode" => true,
        ]);
    }

    public function duplicate(Form $form): RedirectResponse
    {
        $new = DB::transaction(function () use ($form): Form {
            $copy = $form->replicate();
            $copy->name = $form->name . " (" . __("forms.duplicated_name_suffix") . ")";
            $copy->slug = $form->slug . "-copy-" . Str::lower(Str::random(8));
            $copy->save();

            foreach ($form->fields as $field) {
                $copy->fields()->create($field->replicate(["form_id"])->toArray());
            }

            return $copy;
        });

        return redirect()->route("admin.forms.edit", $new)->with("status", __("forms.duplicated"));
    }

    /**
     * Export form definition as JSON.
     */
    public function exportJson(Form $form): Response
    {
        $data = $this->importExport->export($form);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            "Content-Type" => "application/json",
            "Content-Disposition" => "attachment; filename=\"{$form->slug}.json\"",
        ]);
    }

    /**
     * Import form from JSON.
     */
    public function importJson(Request $request): RedirectResponse
    {
        $json = $request->file("json")?->get() ?: $request->input("json");
        if (!$json) {
            return back()->withErrors(["json" => __("forms.import_no_json")]);
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $newForm = $this->importExport->import($data);

            return redirect()->route("admin.forms.edit", $newForm)->with("status", "Форма импортирована.");
        } catch (\JsonException $e) {
            return back()->withErrors(["json" => __("forms.import_invalid_json", ["message" => $e->getMessage()])]);
        } catch (\Exception $e) {
            return back()->withErrors(["import" => __("forms.import_failed", ["message" => $e->getMessage()])]);
        }
    }

    /**
     * Export submissions to CSV.
     */
    public function export(Form $form, Request $request): Response
    {
        $submissions = $form->submissions()
            ->with(["values.field"])
            ->latest()
            ->get();

        $headers = ["ID", "Date", "IP", "User ID", "Status"];
        foreach ($form->fields as $field) {
            $headers[] = $field->label;
        }

        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= implode(",", array_map(fn ($h) => '"' . $h . '"', $headers)) . "\n";

        foreach ($submissions as $sub) {
            $row = [
                $sub->id,
                $sub->created_at->format("Y-m-d H:i"),
                $sub->ip_address,
                $sub->user_id ?? "",
                $sub->status,
            ];

            foreach ($form->fields as $field) {
                $val = $sub->values->firstWhere("field_id", $field->id)?->value ?? "";
                $row[] = '"' . str_replace('"', '""', (string)$val) . '"';
            }

            $csv .= implode(",", $row) . "\n";
        }

        $filename = "form-{$form->slug}-" . now()->format("Y-m-d") . ".csv";

        return response($csv, 200, [
            "Content-Type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
