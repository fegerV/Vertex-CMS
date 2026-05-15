<?php

namespace App\Builder\Http\Controllers;

use App\Builder\Config\BlockRegistry;
use App\Builder\Services\PageRenderer;
use App\Builder\Support\BuilderContractSerializer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuilderApiController extends Controller
{
    public function __construct(
        private readonly PageRenderer $renderer,
        private readonly BuilderContractSerializer $serializer,
    ) {
    }

    public function blocks(): JsonResponse
    {
        $blocks = collect($this->serializer->serializeRegistry(BlockRegistry::all()));

        return response()->json([
            'registry_version' => '1.0',
            'blocks' => $blocks->all(),
            'entries' => $blocks->values()->all(),
            'categories' => BlockRegistry::getCategories(),
            'count' => $blocks->count(),
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
