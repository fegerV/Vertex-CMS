@extends('admin.layouts.app')

@section('title', 'Редактирование таксономии - VertexCMS')
@section('page_title', 'Редактирование таксономии')
@section('page_subtitle', $taxonomy->name)

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
        <section class="space-y-6">
            <div class="vc-toolbar vc-toolbar-sticky">
                <div class="vc-toolbar-meta">
                    <a href="{{ route('admin.taxonomies.index') }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Назад к таксономиям</a>
                    <span class="vc-toolbar-title">{{ $taxonomy->name }}</span>
                    <span class="vc-toolbar-text">Измените настройки архива и управляйте терминами этой таксономии из одного экрана.</span>
                </div>

                <button form="taxonomy-form" class="vc-button vc-button-primary vc-button-large" type="submit">
                    Сохранить таксономию
                </button>
            </div>

            <form id="taxonomy-form" method="POST" action="{{ route('admin.taxonomies.update', $taxonomy) }}" class="vc-form-surface space-y-6">
                @csrf
                @method('PUT')
                @include('admin.taxonomies.partials.form')
            </form>

            <section class="vc-table-wrap">
                <div class="flex items-center justify-between gap-3 border-b border-[var(--vc-border)] px-4 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Термины</h2>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Значения таксономии, которые можно привязывать к страницам и выводить в публичных архивах.</p>
                    </div>
                    @if (auth()->user()?->hasPermission('taxonomy.create'))
                        <a href="{{ route('admin.taxonomies.terms.create', $taxonomy) }}" class="vc-button vc-button-primary">
                            Добавить термин
                        </a>
                    @endif
                </div>
                <table class="vc-table text-sm">
                    <thead>
                        <tr>
                            <th>Название</th>
                            <th>Slug</th>
                            <th>Родитель</th>
                            <th>Страниц</th>
                            <th class="text-right">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($taxonomy->terms as $term)
                            <tr>
                                <td class="font-medium text-[var(--vc-text)]">{{ $term->name }}</td>
                                <td>{{ $term->slug }}</td>
                                <td>{{ $term->parent?->name ?: '—' }}</td>
                                <td><span class="vc-badge">{{ $term->pages_count }}</span></td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        @if (auth()->user()?->hasPermission('taxonomy.edit'))
                                            <a href="{{ route('admin.taxonomies.terms.edit', [$taxonomy, $term]) }}" class="vc-button vc-button-secondary">
                                                Изменить
                                            </a>
                                        @endif
                                        @if (auth()->user()?->hasPermission('taxonomy.delete'))
                                            <form method="POST" action="{{ route('admin.taxonomies.terms.destroy', [$taxonomy, $term]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="vc-button vc-button-danger">
                                                    Удалить
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-[var(--vc-text-muted)]">В этой таксономии пока нет терминов.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </section>

        <aside class="space-y-6">
            <section class="vc-panel p-6">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Публичный архив</h2>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">
                    Базовый URL архива: <span class="font-medium">{{ url('/taxonomy/'.$taxonomy->slug) }}</span>
                </p>
                <p class="mt-3 text-sm text-[var(--vc-text-muted)]">
                    Реальная точка входа строится по термину, например `/taxonomy/{{ $taxonomy->slug }}/services`.
                </p>
            </section>

            @if (auth()->user()?->hasPermission('taxonomy.delete'))
                <section class="vc-panel p-6">
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Опасная зона</h2>
                    <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Удаление таксономии удалит все вложенные термины и связи со страницами.</p>
                    <form method="POST" action="{{ route('admin.taxonomies.destroy', $taxonomy) }}" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button class="vc-button vc-button-danger">
                            Удалить таксономию
                        </button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
@endsection
