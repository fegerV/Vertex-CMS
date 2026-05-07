<?php

namespace App\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaApiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Media::query()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['ok' => true, 'payload' => $request->all()], 201);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        return response()->json(['ok' => true, 'id' => $media->id, 'payload' => $request->all()]);
    }

    public function destroy(Media $media): JsonResponse
    {
        return response()->json(['ok' => true, 'id' => $media->id]);
    }
}

