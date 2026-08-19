<?php

namespace App\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaTag;
use App\Models\MediaVersion;
use App\Media\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        private readonly MediaService $media,
    ) {
    }

    public function index(): View
    {
        $items = Media::query()->with(['tags', 'versions'])->latest()->paginate(24);
        $folders = \App\Models\MediaFolder::withCount('media')->orderBy('name')->get();
        $tags = MediaTag::orderBy('name')->get();

        return view('admin.media.index', [
            'items' => $items,
            'folders' => $folders,
            'tags' => $tags,
            'initialTotalItems' => $items->total(),
            'canManageFolders' => request()->user()?->hasPermission('media.manage_folders') ?? false,
            'canUploadMedia' => request()->user()?->hasPermission('media.upload') ?? false,
            'canEditMedia' => request()->user()?->hasPermission('media.edit') ?? false,
            'canDeleteMedia' => request()->user()?->hasPermission('media.delete') ?? false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf', 'max:10240'],
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ]);

        $this->media->upload($payload['file'], $request->user(), $payload);

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'Файл загружен.');
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $payload = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ]);

        $this->media->update($media, $payload);

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'Метаданные файла сохранены.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        try {
            $this->media->delete($media);

            return redirect()
                ->route('admin.media.index')
                ->with('status', 'Файл удалён.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('admin.media.index')
                ->with('error', $e->getMessage());
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:media,id'],
        ]);

        $result = $this->media->bulkDelete($payload['ids'], $request->user());

        return response()->json([
            'success' => true,
            'deleted' => count($result['deleted']),
            'failed' => count($result['failed']),
            'details' => $result,
        ]);
    }

    public function bulkMove(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:media,id'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        $count = $this->media->bulkMove($payload['ids'], $payload['folder_id'] ?? null, $request->user());

        return response()->json([
            'success' => true,
            'moved' => $count,
        ]);
    }

    public function versions(Media $media): JsonResponse
    {
        $versions = $media->versions()->latest()->get();

        return response()->json([
            'versions' => $versions,
        ]);
    }

    public function revertVersion(MediaVersion $version): RedirectResponse
    {
        $this->media->revertToVersion($version);

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'Файл откатан к предыдущей версии.');
    }

    public function usageStats(Media $media): JsonResponse
    {
        $stats = $this->media->getUsageStats($media);

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function optimize(Media $media): JsonResponse
    {
        $success = $this->media->optimizeImage($media);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Изображение оптимизировано' : 'Не удалось оптимизировать изображение',
        ]);
    }

    public function tags(): JsonResponse
    {
        $tags = MediaTag::withCount('media')->orderBy('name')->get();

        return response()->json([
            'tags' => $tags,
        ]);
    }
}
