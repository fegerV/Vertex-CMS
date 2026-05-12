@extends('admin.layouts.app')

@section('title', 'Роли - VertexCMS')
@section('page_title', 'Роли')
@section('page_subtitle', 'Наборы прав доступа для админки и API')

@section('content')
    <div class="space-y-6">
        <div class="vc-toolbar">
            <div class="vc-toolbar-meta">
                <span class="vc-toolbar-title">Матрица ролей</span>
                <span class="vc-toolbar-text">Роли объединяют permissions и определяют, какие разделы, действия и API-возможности доступны пользователям.</span>
            </div>
        </div>

        <section class="vc-table-wrap">
            <table class="vc-table text-sm">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Slug</th>
                        <th>Разрешений</th>
                        <th class="text-right">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td class="font-medium text-[var(--vc-text)]">{{ $role->name }}</td>
                            <td>{{ $role->slug }}</td>
                            <td><span class="vc-badge">{{ $role->permissions_count }}</span></td>
                            <td class="text-right">
                                @if (auth()->user()?->hasPermission('roles.edit'))
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="vc-button vc-button-secondary">
                                        Изменить
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-[var(--vc-text-muted)]">Ролей пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
@endsection
