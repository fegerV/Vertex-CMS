@extends('admin.layouts.app')

@section('title', 'Редактирование страницы - VertexCMS')
@section('page_title', 'Редактирование страницы')
@section('page_subtitle', $page->title)

@section('content')
    <div class="mx-auto grid max-w-7xl gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.pages.index') }}" class="text-sm text-slate-600 hover:text-slate-950">Назад к страницам</a>
                <a href="{{ route('admin.pages.builder', $page) }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-white">
                    Открыть Builder
                </a>
            </div>

            <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-5 rounded-lg border border-slate-200 bg-white p-6">
                @csrf
                @method('PUT')
                @include('admin.pages.partials.form')
            </form>
        </div>

        @includeWhen(auth()->user()?->hasPermission('ai.use'), 'admin.pages.partials.ai-assistant')
    </div>
@endsection

