@extends('admin.layouts.app')

@section('title', 'Создание страницы - VertexCMS')
@section('page_title', 'Создание страницы')
@section('page_subtitle', 'Базовая информация, контент, SEO и таксономии')

@section('content')
    <div class="mx-auto grid max-w-7xl gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="vc-page-editor">
            <div class="vc-toolbar vc-toolbar-sticky">
                <div class="vc-toolbar-meta">
                    <a href="{{ route('admin.pages.index') }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Назад к страницам</a>
                    <span class="vc-toolbar-title">Новая страница</span>
                    <span class="vc-toolbar-text">Сначала задайте заголовок, статус и шаблон. Builder и публичный URI можно доработать после первого сохранения.</span>
                </div>

                <div class="vc-chip-row">
                    <span class="vc-chip">Черновик по умолчанию</span>
                    <button form="page-editor-form" class="vc-button vc-button-primary vc-button-large" type="submit">
                        Сохранить страницу
                    </button>
                </div>
            </div>

            <form id="page-editor-form" method="POST" action="{{ route('admin.pages.store') }}" class="vc-form-surface space-y-6">
                @csrf
                @include('admin.pages.partials.form')
            </form>
        </div>

        @includeWhen(auth()->user()?->hasPermission('ai.use'), 'admin.pages.partials.ai-assistant')
    </div>
@endsection
