@extends('admin.layouts.app')

@section('title', 'Design Library - VertexCMS')
@section('page_title', 'Design Library')
@section('page_subtitle', 'Шаблоны, стартовые наборы и пресеты для page builder')
@section('page_wrap_class', 'vc-page-wrap-builder')
@section('body_class', 'vc-admin-body-builder')

@section('content')
<div
    id="design-library"
    data-vc-design-library
    data-workspace='@json($workspace)'
    data-api-url="{{ route('admin.pages.builder.design-library.api') }}"
    class="vc-design-library-host"
>
    <div class="vc-panel vc-panel-strong p-6 text-sm text-[var(--vc-text-soft)]">
        Loading design library...
    </div>
</div>
@endsection
