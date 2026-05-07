<?php

namespace App\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SystemApiController extends Controller
{
    public function info(): JsonResponse
    {
        return response()->json([
            'vertex_version' => config('vertex.version'),
            'php_version' => PHP_VERSION,
        ]);
    }

    public function clearCache(): JsonResponse
    {
        return response()->json(['ok' => true]);
    }
}

