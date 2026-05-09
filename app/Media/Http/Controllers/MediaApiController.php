<?php

namespace App\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Media\Services\MediaService;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MediaApiController extends Controller
{
    public function __construct(
        private readonly MediaService $media,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('media.view'), 403);

        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer'],
            'kind' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        $query = Media::query()->latest();

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->input('ids', []));
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function (Builder $builder) use ($term): void {
                $builder
                    ->where('original_filename', 'like', "%{$term}%")
                    ->orWhere('title', 'like', "%{$term}%")
                    ->orWhere('alt', 'like', "%{$term}%")
                    ->orWhere('caption', 'like', "%{$term}%");
            });
        }

        if ($request->input('kind') === 'image') {
            $query->where('mime_type', 'like', 'image/%');
        }

        return response()->json(
            $query
                ->paginate($request->integer('per_page', 18))
                ->through(fn (Media $media) => [
                    'id' => $media->id,
                    'url' => $media->url,
                    'title' => $media->title,
                    'alt' => $media->alt,
                    'caption' => $media->caption,
                    'original_filename' => $media->original_filename,
                    'mime_type' => $media->mime_type,
                    'extension' => $media->extension,
                    'width' => $media->width,
                    'height' => $media->height,
                    'size' => $media->size,
                ])
        );
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('media.upload'), 403);

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
        abort_unless($request->user()?->hasPermission('media.edit'), 403);

        $payload = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ]);

        return response()->json(['ok' => true, 'data' => $this->media->update($media, $payload)]);
    }

    public function destroy(Media $media): JsonResponse
    {
        abort_unless(request()->user()?->hasPermission('media.delete'), 403);

        $this->media->delete($media);

        return response()->json(['ok' => true]);
    }
}
