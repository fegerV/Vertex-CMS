<?php

namespace App\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Media\Services\MediaService;
use App\Models\Media;
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
        $items = Media::query()->latest()->paginate(24);
        $folders = \App\Models\MediaFolder::withCount('media')->orderBy('name')->get();

        return view('admin.media.index', [
            'items' => $items,
            'folders' => $folders,
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
        ]);

        $this->media->update($media, $payload);

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'Метаданные файла сохранены.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        $this->media->delete($media);

        return redirect()
            ->route('admin.media.index')
            ->with('status', 'Файл удалён.');
    }
}
