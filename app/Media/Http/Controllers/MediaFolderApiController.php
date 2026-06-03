<?php

namespace App\Media\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaFolder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaFolderApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('media.view'), 403);

        $folders = MediaFolder::query()
            ->withCount('media')
            ->orderBy('name')
            ->get();

        return response()->json([
            'folders' => $folders->map(fn (MediaFolder $folder) => $this->transformFolder($folder)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('media.manage_folders'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:media_folders,id'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $folder = MediaFolder::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'parent_id' => $data['parent_id'] ?? null,
            'color' => $this->normalizeColor($data['color'] ?? '#6366f1'),
        ]);

        return response()->json(['ok' => true, 'data' => $this->transformFolder($folder->loadCount('media'))], 201);
    }

    public function update(Request $request, MediaFolder $folder): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('media.manage_folders'), 403);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:media_folders,id'],
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        if (isset($data['name'])) {
            $folder->name = $data['name'];
            $folder->slug = Str::slug($data['name']).'-'.Str::random(4);
        }

        if (array_key_exists('parent_id', $data)) {
            abort_if((int) $data['parent_id'] === (int) $folder->id, 422, 'Folder cannot be its own parent.');
            $folder->parent_id = $data['parent_id'];
        }

        if (isset($data['color'])) {
            $folder->color = $this->normalizeColor($data['color']);
        }

        $folder->save();

        return response()->json(['ok' => true, 'data' => $this->transformFolder($folder->loadCount('media'))]);
    }

    public function destroy(Request $request, MediaFolder $folder): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('media.manage_folders'), 403);

        // Move media to root before deleting
        Media::where('folder_id', $folder->id)->update(['folder_id' => null]);

        $folder->delete();

        return response()->json(['ok' => true]);
    }

    private function normalizeColor(string $color): string
    {
        return '#'.strtoupper(ltrim($color, '#'));
    }

    private function transformFolder(MediaFolder $folder): array
    {
        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'slug' => $folder->slug,
            'color' => $folder->color ?: '#6366F1',
            'parent_id' => $folder->parent_id,
            'media_count' => $folder->media_count ?? 0,
        ];
    }
}
