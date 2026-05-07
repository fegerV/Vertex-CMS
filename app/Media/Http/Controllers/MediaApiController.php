<?php

namespace App\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Media\Services\MediaService;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaApiController extends Controller
{
    public function __construct(
        private readonly MediaService $media,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json(Media::query()->paginate());
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf', 'max:10240'],
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ]);

        $media = $this->media->upload($payload['file'], $request->user(), $payload);

        return response()->json(['ok' => true, 'data' => $media], 201);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $payload = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ]);

        return response()->json(['ok' => true, 'data' => $this->media->update($media, $payload)]);
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->media->delete($media);

        return response()->json(['ok' => true]);
    }
}
