@extends('admin.layouts.app')

@section('title', 'Новая страница - VertexCMS')
@section('page_title', 'Новая страница')
@section('page_subtitle', 'Сначала сохраняем черновик, затем открываем Builder и UX Preview')

@section('content')
    <form id="page-editor-form" method="POST" action="{{ route('admin.pages.store') }}">
        @csrf

        <div class="mx-auto max-w-[1540px] space-y-6">
            <section class="vc-toolbar vc-toolbar-sticky">
                <div class="vc-toolbar-meta">
                    <span class="vc-toolbar-title">Новая страница</span>
                    <span class="vc-toolbar-text">Заполните базовые параметры, сохраните черновик и затем продолжайте в визуальном Builder.</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="vc-chip">Editor</span>
                    <span class="vc-chip">UX Preview после сохранения</span>
                    <span class="vc-chip">Builder после сохранения</span>
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
@endsection
