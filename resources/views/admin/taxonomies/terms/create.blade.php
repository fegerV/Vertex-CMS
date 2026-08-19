@extends('admin.layouts.app')

@section('title', 'Создание термина - VertexCMS')
@section('page_title', 'Создание термина')
@section('page_subtitle', $taxonomy->name)

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="vc-toolbar vc-toolbar-sticky">
            <div class="vc-toolbar-meta">
                <a href="{{ route('admin.taxonomies.edit', $taxonomy) }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Назад к таксономии</a>
                <span class="vc-toolbar-title">Новый термин</span>
                <span class="vc-toolbar-text">Термин можно использовать для привязки страниц, публичных архивов и SEO-структуры раздела.</span>
            </div>

            <button form="term-form" class="vc-button vc-button-primary vc-button-large" type="submit">
                Сохранить термин
            </button>
        </div>

        <form id="term-form" method="POST" action="{{ route('admin.taxonomies.terms.store', $taxonomy) }}" class="vc-form-surface space-y-6">
            @csrf
            @include('admin.taxonomies.terms.partials.form')
        </form>
    </div>
@endsection
