<?php

namespace App\Builder\Http\Controllers;

use App\Builder\Config\BlockRegistry;
use App\Builder\Services\PageRenderer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BuilderApiController extends Controller
{
    public function __construct(private readonly PageRenderer $renderer)
    {
    }

    public function blocks(): JsonResponse
    {
        $blocks = collect(BlockRegistry::all())
            ->map(function (array $block, string $type): array {
                $fields = collect($block['fields'] ?? [])
                    ->map(function (array $field): array {
                        if (($field['type'] ?? null) === 'select' && is_array($field['options'] ?? null)) {
                            $field['options'] = collect($field['options'])
                                ->map(fn ($label, $value) => [
                                    'value' => (string) $value,
                                    'label' => (string) $label,
                                ])
                                ->values()
                                ->all();
                        }

                        return $field;
                    })
                    ->all();

                $defaultBlock = (array) ($block['default'] ?? []);
                $defaultSettings = (array) ($defaultBlock['settings'] ?? []);
                $editorKind = in_array($type, ['heading', 'text'], true) ? 'rich-text-capable' : 'custom';

                return array_merge($block, [
                    'type' => $type,
                    'fields' => $fields,
                    'editor' => [
                        'component' => 'vc-builder-block-' . Str::kebab($type),
                        'kind' => $editorKind,
                        'supports' => collect($fields)->pluck('type')->values()->all(),
                    ],
                    'default_block' => [
                        'type' => $type,
                        'settings' => $defaultSettings,
                    ],
                ]);
            })
            ->values()
            ->all();

        return response()->json([
            'registry_version' => '1.0',
            'blocks' => $blocks,
            'categories' => BlockRegistry::getCategories(),
            'count' => count($blocks),
        ]);
    }

    public function renderPreview(Request $request): JsonResponse
    {
        $content = $request->validate([
            'content' => ['required', 'array'],
        ])['content'];

        $layout = $request->input('layout', 'default');
        $sections = is_array($content) ? $content : [];

        $html = $this->renderer->render([
            'version' => '1.0',
            'layout' => $layout,
            'sections' => $sections,
        ]);

        return response()->json([
            'html' => (string) $html,
            'sections_count' => count($sections),
        ]);
    }
}
