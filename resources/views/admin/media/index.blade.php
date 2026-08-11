@extends('admin.layouts.app')

@section('title', 'Медиа - VertexCMS')
@section('page_title', 'Медиатека')
@section('page_subtitle', 'Папки, цвета, загрузка и управление файлами')

@section('page_wrap_class', 'vc-page-wrap-media')

@section('content')
@php
    $initialItems = $items->getCollection()->map(fn ($item) => [
        'id' => $item->id,
        'url' => $item->url,
        'title' => $item->title,
        'alt' => $item->alt,
        'caption' => $item->caption,
        'original_filename' => $item->original_filename,
        'mime_type' => $item->mime_type,
        'extension' => $item->extension,
        'size' => $item->size,
        'width' => $item->width,
        'height' => $item->height,
        'folder_id' => $item->folder_id,
        'created_at' => optional($item->created_at)?->toIso8601String(),
    ])->values();

    $initialFolders = $folders->map(fn ($folder) => [
        'id' => $folder->id,
        'name' => $folder->name,
        'slug' => $folder->slug,
        'color' => $folder->color ?: '#6366F1',
        'parent_id' => $folder->parent_id,
        'media_count' => $folder->media_count,
    ])->values();

    $mediaConfig = [
        'apiBase' => url('/admin/api/media'),
        'folderApiBase' => url('/admin/api/media/folders'),
        'bulkDeleteUrl' => route('admin.media.bulk-delete'),
        'bulkMoveUrl' => route('admin.media.bulk-move'),
        'canManageFolders' => $canManageFolders,
        'canUploadMedia' => $canUploadMedia,
        'canEditMedia' => $canEditMedia,
        'canDeleteMedia' => $canDeleteMedia,
        'initialItems' => $initialItems,
        'initialFolders' => $initialFolders,
        'initialTotalItems' => $initialTotalItems,
    ];
@endphp

<div
    data-vc-media-manager
    data-config='@json($mediaConfig)'
    class="vc-media-manager-host"
></div>
@endsection
