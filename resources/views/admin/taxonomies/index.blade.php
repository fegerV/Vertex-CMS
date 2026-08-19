@extends('admin.layouts.app')

@section('title', 'Таксономии - VertexCMS')
@section('page_title', 'Таксономии')
@section('page_subtitle', 'Категории, теги и структура публичных архивов')

@section('content')
    <div class="space-y-6">
        <div class="vc-toolbar">
            <div class="vc-toolbar-meta">
                <span class="vc-toolbar-title">Классификация контента</span>
                <span class="vc-toolbar-text">Таксономии позволяют группировать страницы по категориям, тегам и другим публичным архивам.</span>
            </div>

            @if (auth()->user()?->hasPermission('taxonomy.create'))
                <a href="{{ route('admin.taxonomies.create') }}" class="vc-button vc-button-primary">
                    Создать таксономию
                </a>
            @endif
        </div>

        <section class="vc-table-wrap">
            <table class="vc-table text-sm">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Сущность</th>
                        <th>Режим</th>
                        <th>Терминов</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taxonomies as $taxonomy)
                        <tr>
                            <td class="font-medium text-[var(--vc-text)]">{{ $taxonomy->name }}</td>
                            <td>{{ $taxonomy->slug }}</td>
                            <td>{{ $taxonomy->entity_type }}</td>
                            <td><span class="vc-badge">{{ $taxonomy->hierarchical ? 'Иерархическая' : 'Плоская' }}</span></td>
                            <td><span class="vc-badge">{{ $taxonomy->terms_count }}</span></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    @if (auth()->user()?->hasPermission('taxonomy.edit'))
                                        <a href="{{ route('admin.taxonomies.edit', $taxonomy) }}" class="vc-button vc-button-secondary">
                                            Управлять
                                        </a>
                                    @endif
                                    @if (auth()->user()?->hasPermission('taxonomy.delete'))
                                        <form method="POST" action="{{ route('admin.taxonomies.destroy', $taxonomy) }}">
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
                            <td colspan="6" class="py-10 text-center text-[var(--vc-text-muted)]">Таксономий пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection
