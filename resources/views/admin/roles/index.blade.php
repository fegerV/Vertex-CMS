@extends('admin.layouts.app')

@section('title', 'Роли - VertexCMS')
@section('page_title', 'Роли')
@section('page_subtitle', 'Наборы прав доступа')

@section('content')
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <table class="w-full border-collapse text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Название</th>
                    <th class="px-4 py-3 font-medium">Slug</th>
                    <th class="px-4 py-3 font-medium">Permissions</th>
                    <th class="px-4 py-3 text-right font-medium">Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $role->name }}</td>
                        <td class="px-4 py-3">{{ $role->slug }}</td>
                        <td class="px-4 py-3">{{ $role->permissions_count }}</td>
                        <td class="px-4 py-3 text-right">
                            @if (auth()->user()?->hasPermission('roles.edit'))
                                <a href="{{ route('admin.roles.edit', $role) }}" class="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
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
