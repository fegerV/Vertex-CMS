<?php

namespace App\Admin\Http\Controllers;

use App\Core\Services\SettingsService;
use App\Core\Support\SettingCatalog;
use App\Http\Controllers\Controller;
use App\System\Services\ActivityLogService;
use Illuminate\Support\Arr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly ActivityLogService $activityLog,
    ) {
    }

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'groups' => SettingCatalog::groups(),
            'values' => $this->settings->allMasked(),
            'canManageAiKeys' => request()->user()?->hasPermission('ai.manage_keys') ?? false,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rawSettings = Arr::dot($request->input('settings', []));
        $rules = collect($this->rules())
            ->filter(fn (array $ruleSet, string $key) => array_key_exists($key, $rawSettings))
            ->all();

        $payload = Arr::dot(Validator::make(
            $request->input('settings', []),
            $rules
        )->validate());

        $payload = $this->normalizeBooleanValues($request, $payload);
        $payload = $this->normalizeJsonValues($request, $payload);
        $payload = $this->filterRestrictedAiFields($request, $payload);

        $this->settings->setMany($payload);

        $secretKeys = collect(array_keys($payload))
            ->intersect(SettingCatalog::secretKeys())
            ->values()
            ->all();

        $this->activityLog->record('settings.edit', 'settings', null, 'Settings updated.', [
            'keys' => array_values(array_diff(array_keys($payload), $secretKeys)),
            'secret_keys_updated' => count($secretKeys),
        ], $request);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', __('settings.save_success'));
    }

    public function changeLocale(Request $request, string $locale): RedirectResponse
    {
        if (in_array($locale, ['en', 'ru'])) {
            $request->session()->put('locale', $locale);
        }

        return redirect()->back();
    }

    private function rules(): array
    {
        return collect(SettingCatalog::definitions())
            ->mapWithKeys(fn (array $definition, string $key) => [$key => $definition['rules'] ?? ['nullable']])
            ->all();
    }

    private function normalizeBooleanValues(Request $request, array $payload): array
    {
        $rawSettings = Arr::dot($request->input('settings', []));

        foreach (SettingCatalog::definitions() as $key => $definition) {
            if (($definition['type'] ?? null) === 'boolean') {
                if (! array_key_exists($key, $rawSettings)) {
                    continue;
                }

                $payload[$key] = filter_var($rawSettings[$key], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $payload;
    }

    private function normalizeJsonValues(Request $request, array $payload): array
    {
        $rawSettings = Arr::dot($request->input('settings', []));

        foreach (SettingCatalog::definitions() as $key => $definition) {
            if (($definition['type'] ?? null) === 'json') {
                if (! array_key_exists($key, $rawSettings)) {
                    continue;
                }
                $value = $rawSettings[$key];
                if (is_string($value) && trim($value) === '') {
                    $payload[$key] = [];
                } elseif (is_string($value)) {
                    $decoded = json_decode($value, true);
                    $payload[$key] = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                }
            }
        }

        return $payload;
    }

    private function filterRestrictedAiFields(Request $request, array $payload): array
    {
        if ($request->user()?->hasPermission('ai.manage_keys')) {
            return $payload;
        }

        foreach (SettingCatalog::secretKeys() as $secretKey) {
            if (str_starts_with($secretKey, 'ai.')) {
                unset($payload[$secretKey]);
            }
        }

        return $payload;
    }
}
