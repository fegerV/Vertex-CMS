@extends('admin.layouts.app')

@section('title', 'Роли - VertexCMS')
@section('page_title', 'Роли')
@section('page_subtitle', 'Наборы прав доступа')

@section('content')
    <section class="vc-table-wrap">
        <table class="vc-table text-sm">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Slug</th>
                    <th>Permissions</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td class="font-medium text-[var(--vc-text)]">{{ $role->name }}</td>
                        <td>{{ $role->slug }}</td>
                        <td><span class="vc-badge">{{ $role->permissions_count }}</span></td>
                        <td class="text-right">
                            @if (auth()->user()?->hasPermission('roles.edit'))
                                <a href="{{ route('admin.roles.edit', $role) }}" class="vc-button vc-button-secondary px-3 py-2">
                                    Изменить
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
