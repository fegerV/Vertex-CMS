@extends('admin.layouts.app')

@section('title', 'Редактирование страницы - VertexCMS')
@section('page_title', 'Редактирование страницы')
@section('page_subtitle', 'WordPress-подобный экран редактирования с отдельным Builder и UX Preview')

@section('content')
    <form id="page-editor-form" method="POST" action="{{ route('admin.pages.update', $page) }}">
        @csrf
        @method('PUT')

        <div class="mx-auto max-w-[1540px] space-y-6">
            <section class="vc-toolbar vc-toolbar-sticky">
                <div class="vc-toolbar-meta">
                    <span class="vc-toolbar-title">Страница: {{ $page->title }}</span>
                    <span class="vc-toolbar-text">Редактор страницы, SEO и атрибуты собраны на одном экране, визуальный Builder открыт отдельно.</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="vc-chip">Editor</span>
                    <a href="{{ route('admin.pages.preview', $page) }}" target="_blank" rel="noopener" class="vc-chip">UX Preview</a>
                    <a href="{{ route('admin.pages.builder', $page) }}" class="vc-chip">Builder</a>
                    <a href="{{ route('admin.pages.index') }}" class="vc-button vc-button-secondary px-4 py-3">Ко всем страницам</a>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start">
                <div class="space-y-6">
                    @include('admin.pages.partials.form')
                </div>

                @include('admin.pages.partials.wp-sidebar')
            </div>
        </div>
    </form>

    @if ($page->exists)
        <form id="page-delete-form" method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
