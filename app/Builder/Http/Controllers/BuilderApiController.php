<?php

namespace App\Builder\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuilderApiController extends Controller
{
    public function blocks(): JsonResponse
    {
        return response()->json([
            'blocks' => ['heading', 'text', 'button', 'image', 'video', 'gallery', 'cards', 'form-placeholder', 'html', 'divider', 'faq'],
        ]);
    }

    public function renderPreview(Request $request): JsonResponse
    {
        return response()->json([
            'html' => '<div>Preview placeholder</div>',
            'payload' => $request->all(),
        ]);
    }
}

