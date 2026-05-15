<?php

namespace App\Builder\Services;

use App\Core\Services\SettingsService;
use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Manages shared presets and templates for the builder.
 */
class BuilderLibraryService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    /**
     * Get visible presets for the current user.
     */
    public function getVisiblePresets($request): array
    {
        return collect($this->getAllPresets())
            ->filter(fn (array $preset) => $this->canViewLibraryItem($preset, $request))
            ->map(fn (array $preset) => $this->decorateLibraryItem($preset, $request))
            ->values()
            ->all();
    }

    /**
     * Create a new preset.
     */
    public function createPreset(array $data, $request): array
    {
        $preset = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'type' => $data['type'],
            'settings' => $data['settings'],
            'visibility' => $data['visibility'] ?? 'shared',
            'owner_id' => $request->user()?->id,
            'owner_name' => $request->user()?->name,
            'updated_at' => now()->toIso8601String(),
            'created_by' => $request->user()?->name,
        ];

        $presets = $this->getAllPresets();
        array_unshift($presets, $preset);
        $this->storePresets($presets);

        return $preset;
    }

    /**
     * Update an existing preset.
     */
    public function updatePreset(string $presetId, array $data, $request): array
    {
        $presets = collect($this->getAllPresets())->map(function (array $preset) use ($presetId, $data, $request) {
            if (($preset['id'] ?? null) !== $presetId) {
                return $preset;
            }

            return [
                ...$preset,
                ...$data,
                'updated_at' => now()->toIso8601String(),
                'updated_by' => $request->user()?->name,
            ];
        })->values()->all();

        $this->storePresets($presets);

        return collect($presets)->firstWhere('id', $presetId);
    }

    /**
     * Delete a preset.
     */
    public function deletePreset(string $presetId): void
    {
        $presets = collect($this->getAllPresets())
            ->reject(fn (array $preset) => ($preset['id'] ?? null) === $presetId)
            ->values()
            ->all();

        $this->storePresets($presets);
    }

    /**
     * Get visible templates for the current user.
     */
    public function getVisibleTemplates($request): array
    {
        $builtinTemplates = [
            [
                'id' => 'hero-banner',
                'name' => 'Hero Banner',
                'category' => 'landing',
                'source' => 'builtin',
                'visibility' => 'shared',
                'sections' => [
                    [
                        'settings' => ['background_color' => '#f8fafc', 'padding_top' => 48, 'padding_bottom' => 48],
                        'blocks' => [
                            ['type' => 'heading', 'settings' => ['level' => 'h1', 'text' => 'Page headline', 'align' => 'center']],
                            ['type' => 'text', 'settings' => ['content' => 'Describe the main value proposition in one short paragraph.', 'align' => 'center']],
                            ['type' => 'button', 'settings' => ['text' => 'Get started', 'url' => '#', 'style' => 'primary']],
                        ],
                    ],
                ],
            ],
            [
                'id' => 'faq-section',
                'name' => 'FAQ Section',
                'category' => 'content',
                'source' => 'builtin',
                'visibility' => 'shared',
                'sections' => [
                    [
                        'settings' => ['padding_top' => 32, 'padding_bottom' => 32],
                        'blocks' => [
                            ['type' => 'heading', 'settings' => ['level' => 'h2', 'text' => 'Frequently asked questions']],
                            ['type' => 'faq', 'settings' => ['items' => [
                                ['question' => 'How does it work?', 'answer' => 'Add your answer here.'],
                                ['question' => 'Can I customize it?', 'answer' => 'Yes, each block can be edited.'],
                            ]]],
                        ],
                    ],
                ],
            ],
        ];

        return collect(array_merge($builtinTemplates, $this->getAllSharedTemplates()))
            ->filter(fn (array $template) => $this->canViewLibraryItem($template, $request))
            ->map(fn (array $template) => $this->decorateLibraryItem($template, $request))
            ->values()
            ->all();
    }

    /**
     * Create a shared template.
     */
    public function createTemplate(array $data, array $normalizedSections, $request): array
    {
        $template = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'category' => $data['category'] ?? 'custom',
            'sections' => $normalizedSections,
            'visibility' => $data['visibility'] ?? 'shared',
            'owner_id' => $request->user()?->id,
            'owner_name' => $request->user()?->name,
            'updated_at' => now()->toIso8601String(),
            'source' => 'shared',
        ];

        $templates = $this->getAllSharedTemplates();
        array_unshift($templates, $template);
        $this->storeSharedTemplates($templates);

        return $template;
    }

    /**
     * Update a shared template.
     */
    public function updateTemplate(string $templateId, array $data, array $normalizedSections = null, $request): array
    {
        $templates = collect($this->getAllSharedTemplates())->map(function (array $template) use ($templateId, $data, $normalizedSections, $request) {
            if (($template['id'] ?? null) !== $templateId) {
                return $template;
            }

            $result = [...$template, ...$data, 'updated_at' => now()->toIso8601String(), 'updated_by' => $request->user()?->name];
            if ($normalizedSections !== null) {
                $result['sections'] = $normalizedSections;
            }

            return $result;
        })->values()->all();

        $this->storeSharedTemplates($templates);

        return collect($templates)->firstWhere('id', $templateId);
    }

    /**
     * Delete a shared template.
     */
    public function deleteTemplate(string $templateId): void
    {
        $templates = collect($this->getAllSharedTemplates())
            ->reject(fn (array $template) => ($template['id'] ?? null) === $templateId)
            ->values()
            ->all();

        $this->storeSharedTemplates($templates);
    }

    protected function getAllPresets(): array
    {
        $value = $this->settings->get('builder.shared_presets', []);

        return is_array($value) ? array_values($value) : [];
    }

    protected function storePresets(array $presets): void
    {
        Setting::query()->updateOrCreate(
            ['group_name' => 'builder', 'setting_key' => 'shared_presets'],
            [
                'setting_value' => json_encode(array_values($presets), JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'autoload' => true,
            ],
        );

        $this->settings->forgetCache();
    }

    protected function getAllSharedTemplates(): array
    {
        $value = $this->settings->get('builder.shared_templates', []);

        return is_array($value) ? array_values($value) : [];
    }

    protected function storeSharedTemplates(array $templates): void
    {
        Setting::query()->updateOrCreate(
            ['group_name' => 'builder', 'setting_key' => 'shared_templates'],
            [
                'setting_value' => json_encode(array_values($templates), JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'autoload' => true,
            ],
        );

        $this->settings->forgetCache();
    }

    protected function canViewLibraryItem(array $item, $request): bool
    {
        if (($item['source'] ?? null) === 'builtin') {
            return true;
        }

        if (($item['visibility'] ?? 'shared') === 'shared') {
            return true;
        }

        return (int) ($item['owner_id'] ?? 0) === (int) ($request->user()?->id ?? 0) || $this->isSuperAdmin($request);
    }

    protected function canManageLibraryItem(array $item, $request): bool
    {
        if (($item['source'] ?? null) === 'builtin') {
            return false;
        }

        return (int) ($item['owner_id'] ?? 0) === (int) ($request->user()?->id ?? 0) || $this->isSuperAdmin($request);
    }

    protected function decorateLibraryItem(array $item, $request): array
    {
        $isOwner = (int) ($item['owner_id'] ?? 0) === (int) ($request->user()?->id ?? 0);
        $isBuiltin = ($item['source'] ?? null) === 'builtin';

        return [
            ...$item,
            'source' => $item['source'] ?? 'shared',
            'visibility' => $item['visibility'] ?? 'shared',
            'can_edit' => ! $isBuiltin && ($isOwner || $this->isSuperAdmin($request)),
            'can_delete' => ! $isBuiltin && ($isOwner || $this->isSuperAdmin($request)),
            'owner' => $item['owner_name'] ?? null,
        ];
    }

    protected function isSuperAdmin($request): bool
    {
        return (bool) $request->user()?->roles()->where('slug', 'super-admin')->exists();
    }
}