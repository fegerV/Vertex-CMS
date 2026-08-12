<?php

namespace Vertex\Forms\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Vertex\Forms\FieldTypeRegistry;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormField;
use Vertex\Forms\Services\FormImportExportService;
use Vertex\Forms\Services\FormService;

class FormApiController extends Controller
{
    public function __construct(
        private readonly FormImportExportService $importExport,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Form::query()->orderBy('sort_order');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $forms = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'forms' => $forms->through(fn ($form) => [
                'id' => $form->id,
                'name' => $form->name,
                'slug' => $form->slug,
                'type' => $form->type,
                'is_active' => $form->is_active,
                'submissions_count' => $form->submissions()->count(),
                'created_at' => $form->created_at?->toDateTimeString(),
            ]),
            'pagination' => [
                'total' => $forms->total(),
                'per_page' => $forms->perPage(),
                'current_page' => $forms->currentPage(),
                'last_page' => $forms->lastPage(),
            ],
        ]);
    }

    public function fieldRegistry(): JsonResponse
    {
        return response()->json(FieldTypeRegistry::getRegistryPayload());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->formRules());

        $form = DB::transaction(function () use ($request, $validated): Form {
            $form = Form::query()->create([
                'name' => $validated['name'],
                'slug' => $this->resolveSlug($validated['slug'] ?? null, $validated['name']),
                'type' => $validated['type'] ?? 'standard',
                'description' => $validated['description'] ?? null,
                'settings' => $validated['settings'] ?? [],
                'require_login' => $validated['require_login'] ?? false,
                'entry_limit' => $validated['entry_limit'] ?? null,
                'daily_limit' => $validated['daily_limit'] ?? null,
                'available_from' => $validated['available_from'] ?? null,
                'available_to' => $validated['available_to'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            if (! empty($validated['fields'])) {
                $this->syncFields($form, $validated['fields']);
            }

            return $form;
        });

        return response()->json([
            'form' => $form->load('fields'),
        ], 201);
    }

    public function show(Form $form): JsonResponse
    {
        $form->load('fields');

        return response()->json([
            'form' => [
                'id' => $form->id,
                'name' => $form->name,
                'slug' => $form->slug,
                'type' => $form->type,
                'description' => $form->description,
                'settings' => $form->settings,
                'is_active' => $form->is_active,
                'fields' => $form->fields->map(fn ($field) => [
                    'id' => $field->id,
                    'name' => $field->name,
                    'label' => $field->label,
                    'type' => $field->type,
                    'sort_order' => $field->sort_order,
                    'required' => $field->required,
                    'visible' => $field->visible,
                    'options' => $field->options,
                    'default_value' => $field->default_value,
                    'placeholder' => $field->placeholder,
                    'help_text' => $field->help_text,
                    'css_class' => $field->css_class,
                    'conditional' => $field->options['conditional'] ?? null,
                ])->values(),
            ],
        ]);
    }

    public function update(Request $request, Form $form): JsonResponse
    {
        $validated = $request->validate($this->formRules($form));

        DB::transaction(function () use ($form, $validated): void {
            if (config('forms.auto_snapshot_on_save', true)) {
                app(FormService::class)->createSnapshot(
                    $form,
                    'Automatic snapshot before update',
                    request()->user()?->id
                );
            }
            $form->update([
                'name' => $validated['name'],
                'slug' => $this->resolveSlug($validated['slug'] ?? null, $validated['name'], $form->id),
                'type' => $validated['type'] ?? $form->type,
                'description' => $validated['description'] ?? null,
                'settings' => $validated['settings'] ?? $form->settings,
                'require_login' => $validated['require_login'] ?? $form->require_login,
                'entry_limit' => $validated['entry_limit'] ?? $form->entry_limit,
                'daily_limit' => $validated['daily_limit'] ?? $form->daily_limit,
                'available_from' => $validated['available_from'] ?? $form->available_from,
                'available_to' => $validated['available_to'] ?? $form->available_to,
                'is_active' => $validated['is_active'] ?? $form->is_active,
                'sort_order' => $validated['sort_order'] ?? $form->sort_order,
            ]);

            if (array_key_exists('fields', $validated)) {
                $this->syncFields($form, $validated['fields']);
            }
        });

        return response()->json([
            'form' => $form->fresh()->load('fields'),
        ]);
    }

    public function destroy(Form $form): JsonResponse
    {
        $form->delete();

        return response()->json(['ok' => true]);
    }

    public function duplicate(Request $request, Form $form): JsonResponse
    {
        $newForm = $form->replicate();
        $newForm->name = $form->name.' (copy)';
        $newForm->slug = $this->resolveSlug($form->slug.'-copy', $form->name.' copy');
        $newForm->save();

        foreach ($form->fields as $field) {
            $payload = $field->replicate(['form_id'])->toArray();
            unset($payload['id']);
            $newForm->fields()->create($payload);
        }

        return response()->json([
            'form' => $newForm->load('fields'),
        ], 201);
    }

    public function exportJson(Form $form): JsonResponse
    {
        $data = $this->importExport->export($form);

        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$form->slug.'.json"',
        ]);
    }

    public function importJson(Request $request, Form $form): JsonResponse
    {
        $json = $request->file('json')?->get() ?: $request->input('json');

        if (! $json) {
            return response()->json(['error' => 'No JSON provided'], 400);
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            $newForm = $this->importExport->import($data);

            return response()->json([
                'message' => 'Form imported successfully',
                'form' => $newForm->load('fields'),
            ], 201);
        } catch (\JsonException $exception) {
            return response()->json(['error' => 'Invalid JSON: '.$exception->getMessage()], 400);
        } catch (\Throwable $exception) {
            Log::error('Form import failed: '.$exception->getMessage());

            return response()->json(['error' => 'Import failed: '.$exception->getMessage()], 500);
        }
    }

    protected function resolveSlug(?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $name);
        $base = $base !== '' ? $base : 'form';
        $candidate = Str::limit($base, 100, '');
        $suffix = 1;

        while (
            Form::query()
                ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $postfix = '-'.$suffix;
            $candidate = Str::limit($base, 100 - strlen($postfix), '').$postfix;
            $suffix++;
        }

        return $candidate;
    }

    protected function syncFields(Form $form, array $fields): void
    {
        $existingIds = [];

        foreach (array_values($fields) as $index => $fieldData) {
            $payload = $this->normalizeFieldPayload((array) $fieldData, $index);

            if (! empty($fieldData['id'])) {
                $field = FormField::query()
                    ->where('form_id', $form->id)
                    ->find($fieldData['id']);

                if ($field !== null) {
                    $field->update($payload);
                    $existingIds[] = $field->id;

                    continue;
                }
            }

            $newField = $form->fields()->create($payload);
            $existingIds[] = $newField->id;
        }

        $query = $form->fields();
        if ($existingIds !== []) {
            $query->whereNotIn('id', $existingIds);
        }
        $query->delete();
    }

    protected function normalizeFieldPayload(array $fieldData, int $index): array
    {
        $type = (string) ($fieldData['type'] ?? 'text');
        $defaultField = FieldTypeRegistry::createDefault($type, [
            'sort_order' => $index,
        ]);

        return [
            'name' => $fieldData['name'] ?? $defaultField['name'],
            'label' => $fieldData['label'] ?? $defaultField['label'],
            'type' => $type,
            'sort_order' => $fieldData['sort_order'] ?? $index,
            'required' => (bool) ($fieldData['required'] ?? $defaultField['required'] ?? false),
            'visible' => (bool) ($fieldData['visible'] ?? $defaultField['visible'] ?? true),
            'options' => array_merge(
                $defaultField['options'] ?? [],
                collect($defaultField)->except(['id', 'type', 'name', 'label', 'sort_order', 'required', 'visible', 'default_value', 'placeholder', 'help_text', 'css_class', 'options'])->all(),
                is_array($fieldData['options'] ?? null) ? $fieldData['options'] : []
            ),
            'default_value' => $fieldData['default_value'] ?? $defaultField['default_value'] ?? null,
            'placeholder' => $fieldData['placeholder'] ?? $defaultField['placeholder'] ?? null,
            'help_text' => $fieldData['help_text'] ?? $defaultField['help_text'] ?? null,
            'css_class' => $fieldData['css_class'] ?? $defaultField['css_class'] ?? null,
        ];
    }

    private function formRules(?Form $form = null): array
    {
        $fieldTypes = collect(FieldTypeRegistry::getAll())->pluck('type')->all();

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('forms', 'slug')->ignore($form?->id)],
            'type' => ['nullable', 'string', 'in:standard,calculator,survey,poll'],
            'description' => ['nullable', 'string', 'max:2000'],
            'settings' => ['nullable', 'array'],
            'require_login' => ['nullable', 'boolean'],
            'entry_limit' => ['nullable', 'integer', 'min:0'],
            'daily_limit' => ['nullable', 'integer', 'min:0'],
            'available_from' => ['nullable', 'date'],
            'available_to' => ['nullable', 'date', 'after_or_equal:available_from'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'fields' => ['nullable', 'array'],
            'fields.*' => ['array'],
            'fields.*.id' => ['nullable', 'integer'],
            'fields.*.type' => ['required', 'string', Rule::in($fieldTypes)],
            'fields.*.name' => ['required', 'string', 'max:100', 'regex:/^[a-z_][a-z0-9_]*$/i', 'distinct'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.options' => ['nullable', 'array'],
            'fields.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.visible' => ['nullable', 'boolean'],
        ];
    }
}
