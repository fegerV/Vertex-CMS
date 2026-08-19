@extends('admin.layouts.app')

@section('title', 'Создание таксономии - VertexCMS')
@section('page_title', 'Создание таксономии')
@section('page_subtitle', 'Новая структура для категорий, тегов и архивов')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="vc-toolbar vc-toolbar-sticky">
            <div class="vc-toolbar-meta">
                <a href="{{ route('admin.taxonomies.index') }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Назад к таксономиям</a>
                <span class="vc-toolbar-title">Новая таксономия</span>
                <span class="vc-toolbar-text">Определите название, slug и базовые свойства архива, чтобы использовать таксономию на страницах и в API.</span>
            </div>

            <button form="taxonomy-form" class="vc-button vc-button-primary vc-button-large" type="submit">
                Сохранить таксономию
            </button>
        </div>

        <form id="taxonomy-form" method="POST" action="{{ route('admin.taxonomies.store') }}" class="vc-form-surface space-y-6">
            @csrf
            @include('admin.taxonomies.partials.form')
        </form>
    </div>
@endsection
