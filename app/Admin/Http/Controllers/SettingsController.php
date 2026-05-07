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
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $payload = Validator::make(
            Arr::dot($request->input('settings', [])),
            $this->rules()
        )->validate();

        $payload = $this->normalizeBooleanValues($request, $payload);

        $this->settings->setMany($payload);
        $this->activityLog->record('settings.edit', 'settings', null, 'Settings updated.', [
            'keys' => array_keys($payload),
        ], $request);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Настройки сохранены.');
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
                $payload[$key] = array_key_exists($key, $rawSettings)
                    ? filter_var($rawSettings[$key], FILTER_VALIDATE_BOOLEAN)
                    : false;
            }
        }

        return $payload;
    }
}
