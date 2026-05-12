@extends('admin.layouts.app')

@section('title', 'Редактирование термина - VertexCMS')
@section('page_title', 'Редактирование термина')
@section('page_subtitle', $term->name)

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <div class="vc-toolbar vc-toolbar-sticky">
                <div class="vc-toolbar-meta">
                    <a href="{{ route('admin.taxonomies.edit', $taxonomy) }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Назад к таксономии</a>
                    <span class="vc-toolbar-title">{{ $term->name }}</span>
                    <span class="vc-toolbar-text">Обновите свойства термина, SEO архива и место в иерархии таксономии.</span>
                </div>

                <button form="term-form" class="vc-button vc-button-primary vc-button-large" type="submit">
                    Сохранить термин
                </button>
            </div>

            <form id="term-form" method="POST" action="{{ route('admin.taxonomies.terms.update', [$taxonomy, $term]) }}" class="vc-form-surface space-y-6">
                @csrf
                @method('PUT')
                @include('admin.taxonomies.terms.partials.form')
            </form>
        </div>

        <aside class="space-y-6">
            <section class="vc-panel p-6">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Использование</h2>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Привязанных страниц: <span class="vc-badge">{{ $term->pages_count }}</span></p>
                <p class="mt-3 text-sm text-[var(--vc-text-muted)]">
                    URL архива:
                    <a href="{{ route('frontend.term-archive', [$taxonomy->slug, $term->slug]) }}" class="underline" target="_blank">
                        {{ route('frontend.term-archive', [$taxonomy->slug, $term->slug]) }}
                    </a>
                </p>
            </section>

            @if (auth()->user()?->hasPermission('taxonomy.delete'))
                <section class="vc-panel p-6">
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Опасная зона</h2>
                    <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Удаление термина также удалит связи этого термина со страницами.</p>
                    <form method="POST" action="{{ route('admin.taxonomies.terms.destroy', [$taxonomy, $term]) }}" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button class="vc-button vc-button-danger">
                            Удалить термин
                        </button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
@endsection
