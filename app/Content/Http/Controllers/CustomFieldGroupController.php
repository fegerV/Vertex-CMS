<?php

namespace App\Content\Http\Controllers;

use App\Content\Services\PageService;
use App\Http\Controllers\Controller;
use App\Models\CustomFieldGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomFieldGroupController extends Controller
{
    public function __construct(
        private readonly PageService $pages,
    ) {
    }

    public function index(): JsonResponse
    {
        $template = request()->string('template')->toString();
        $groups = CustomFieldGroup::query()
            ->orderBy('name')
            ->get();

        if ($template !== '') {
            $groups = $groups
                ->filter(fn (CustomFieldGroup $group) => $group->appliesToPageTemplate($template))
                ->values();
        }

        return response()->json([
            'ok' => true,
            'groups' => $groups,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['nullable', 'string', 'max:255', Rule::unique('custom_field_groups', 'handle')],
            'description' => ['nullable', 'string', 'max:1000'],
            'scope' => ['nullable', 'string', Rule::in(['all_pages', 'template', 'except_template'])],
            'rules' => ['nullable', 'array'],
            'fields' => ['required', 'array'],
        ]);

        $group = CustomFieldGroup::query()->create([
            'name' => $payload['name'],
            'handle' => $this->uniqueHandle($payload['handle'] ?? $payload['name']),
            'description' => $payload['description'] ?? null,
            'scope' => $payload['scope'] ?? 'all_pages',
            'fields_json' => $this->pages->normalizeCustomFieldsPayload($payload['fields']),
            'rules_json' => $this->normalizeRules($payload['scope'] ?? 'all_pages', $payload['rules'] ?? []),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json([
            'ok' => true,
            'group' => $group,
        ]);
    }

    public function update(Request $request, CustomFieldGroup $customFieldGroup): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['nullable', 'string', 'max:255', Rule::unique('custom_field_groups', 'handle')->ignore($customFieldGroup->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'scope' => ['nullable', 'string', Rule::in(['all_pages', 'template', 'except_template'])],
            'rules' => ['nullable', 'array'],
            'fields' => ['required', 'array'],
        ]);

        $customFieldGroup->forceFill([
            'name' => $payload['name'],
            'handle' => $this->uniqueHandle($payload['handle'] ?? $payload['name'], $customFieldGroup->id),
            'description' => $payload['description'] ?? null,
            'scope' => $payload['scope'] ?? $customFieldGroup->scope ?? 'all_pages',
            'fields_json' => $this->pages->normalizeCustomFieldsPayload($payload['fields']),
            'rules_json' => $this->normalizeRules($payload['scope'] ?? $customFieldGroup->scope ?? 'all_pages', $payload['rules'] ?? $customFieldGroup->rules_json ?? []),
            'updated_by' => $request->user()?->id,
        ])->save();

        return response()->json([
            'ok' => true,
            'group' => $customFieldGroup->fresh(),
        ]);
    }

    public function destroy(CustomFieldGroup $customFieldGroup): JsonResponse
    {
        abort_if($customFieldGroup->is_system, 422, 'System field groups cannot be deleted.');

        $customFieldGroup->delete();

        return response()->json([
            'ok' => true,
        ]);
    }

    private function uniqueHandle(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source, '_');
        $handle = $base !== '' ? $base : 'field_group';
        $counter = 1;

        while (
            CustomFieldGroup::query()
                ->where('handle', $handle)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $counter++;
            $handle = "{$base}_{$counter}";
        }

        return $handle;
    }

    private function normalizeRules(string $scope, array $rules): array
    {
        $scope = trim($scope) !== '' ? $scope : 'all_pages';

        return match ($scope) {
            'template', 'except_template' => [
                'templates' => array_values(array_filter(array_map(
                    fn ($template) => trim((string) $template),
                    is_array($rules['templates'] ?? null) ? $rules['templates'] : []
                ))),
            ],
            default => [],
        };
    }
}
