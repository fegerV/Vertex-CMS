@extends('admin.layouts.app')

@section('title', 'UX Builder - ' . $page->title)
@section('page_title', 'UX Builder')
@section('page_subtitle', $page->title)

@section('content')
<div
    id="advanced-builder"
    data-vc-advanced-builder
    data-page='@json($page)'
    data-config='@json($config)'
    data-initial-sections='@json($page->content_json["sections"] ?? [])'
    class="vc-builder-shell-host"
>
    <div class="vc-panel vc-panel-strong p-6 text-sm text-[var(--vc-text-soft)]">
        Loading builder...
    </div>
</div>
@endsection
