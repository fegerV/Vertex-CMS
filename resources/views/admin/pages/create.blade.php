@extends('admin.layouts.app')

@section('title', 'Создание страницы - VertexCMS')
@section('page_title', 'Создание страницы')
@section('page_subtitle', 'Базовая информация, JSON-контент и SEO')

@section('content')
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('admin.pages.index') }}" class="mb-4 inline-block text-sm text-slate-600 hover:text-slate-950">Назад к страницам</a>

        <form method="POST" action="{{ route('admin.pages.store') }}" class="space-y-5 rounded-lg border border-slate-200 bg-white p-6">
            @csrf
            @include('admin.pages.partials.form')
        </form>
    </div>
@endsection

