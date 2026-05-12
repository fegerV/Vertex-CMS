@extends('admin.layouts.app')

@section('title', 'Редактирование страницы - VertexCMS')
@section('page_title', 'Редактирование страницы')
@section('page_subtitle', $page->title)

@section('content')
    <div class="mx-auto grid max-w-7xl gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="vc-page-editor">
            <div class="vc-toolbar vc-toolbar-sticky">
                <div class="vc-toolbar-meta">
                    <a href="{{ route('admin.pages.index') }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Назад к страницам</a>
                    <span class="vc-toolbar-title">{{ $page->title }}</span>
                    <span class="vc-toolbar-text">Редактируйте структуру страницы, SEO и таксономии. Для визуальной сборки откройте builder.</span>
                </div>

                <div class="vc-chip-row">
                    <span class="vc-chip">Статус: {{ $page->status }}</span>
                    <a href="{{ route('admin.pages.builder', $page) }}" class="vc-button vc-button-secondary">
                        Открыть builder
                    </a>
                    <button form="page-editor-form" class="vc-button vc-button-primary vc-button-large" type="submit">
                        Сохранить страницу
                    </button>
                </div>
            </div>

            <form id="page-editor-form" method="POST" action="{{ route('admin.pages.update', $page) }}" class="vc-form-surface space-y-6">
                @csrf
                @method('PUT')
                @include('admin.pages.partials.form')
            </form>
        </div>

        @includeWhen(auth()->user()?->hasPermission('ai.use'), 'admin.pages.partials.ai-assistant')
    </div>
@endsection
