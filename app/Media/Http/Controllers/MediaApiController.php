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
            'type' => ['nullable', 'in:all,image,pdf,document'],
            'sort' => ['nullable', 'in:created_at_desc,created_at_asc,name_asc,name_desc,size_desc,size_asc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:60'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        $query = Media::query();

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

        if ($request->filled('folder_id')) {
            $query->where('folder_id', $request->integer('folder_id'));
        }

        if ($request->input('kind') === 'image') {
            $query->where('mime_type', 'like', 'image/%');
        }

        match ($request->input('type', 'all')) {
            'image' => $query->where('mime_type', 'like', 'image/%'),
            'pdf' => $query->where('mime_type', 'application/pdf'),
            'document' => $query->where('mime_type', 'not like', 'image/%')
                ->where('mime_type', '!=', 'application/pdf'),
            default => null,
        };

        match ($request->input('sort', 'created_at_desc')) {
            'created_at_asc' => $query->oldest(),
            'name_asc' => $query->orderBy('original_filename'),
            'name_desc' => $query->orderByDesc('original_filename'),
            'size_desc' => $query->orderByDesc('size'),
            'size_asc' => $query->orderBy('size'),
            default => $query->latest(),
        };

        return response()->json(
            $query
                ->paginate($request->integer('per_page', 18))
                ->through(fn (Media $media) => $this->transformMedia($media))
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
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        $media = $this->media->upload($payload['file'], $request->user(), $payload);

        return response()->json(['ok' => true, 'data' => $this->transformMedia($media->fresh())], 201);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('media.edit'), 403);

        $payload = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        $updated = $this->media->update($media, $payload);

        return response()->json(['ok' => true, 'data' => $this->transformMedia($updated->fresh())]);
    }

    public function destroy(Media $media): JsonResponse
    {
        abort_unless(request()->user()?->hasPermission('media.delete'), 403);

        $this->media->delete($media);

        return response()->json(['ok' => true]);
    }

    public function move(Request $request, Media $media): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('media.edit'), 403);

        $data = $request->validate([
            'folder_id' => ['nullable', 'exists:media_folders,id'],
        ]);

        $this->media->move($media, $data['folder_id'] ?? null);

        return response()->json(['ok' => true, 'data' => $this->transformMedia($media->fresh())]);
    }

    private function transformMedia(Media $media): array
    {
        return [
            'id' => $media->id,
            'url' => $media->url,
            'title' => $media->title,
            'alt' => $media->alt,
            'caption' => $media->caption,
            'original_filename' => $media->original_filename,
            'mime_type' => $media->mime_type,
            'extension' => $media->extension,
            'size' => $media->size,
            'width' => $media->width,
            'height' => $media->height,
            'folder_id' => $media->folder_id,
            'created_at' => optional($media->created_at)?->toIso8601String(),
        ];
    }
}
