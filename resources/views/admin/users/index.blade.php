@extends('admin.layouts.app')

@section('title', 'Пользователи - VertexCMS')
@section('page_title', 'Пользователи')
@section('page_subtitle', 'Управление аккаунтами и ролями')

@section('content')
    @if (auth()->user()?->hasPermission('users.create'))
        <div class="mb-6 flex justify-end">
            <a href="{{ route('admin.users.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Создать пользователя
            </a>
        </div>
    @endif

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <table class="w-full border-collapse text-left text-sm">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Имя</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Статус</th>
                    <th class="px-4 py-3 font-medium">Роли</th>
                    <th class="px-4 py-3 text-right font-medium">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->status }}</td>
                        <td class="px-4 py-3">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                @if (auth()->user()?->hasPermission('users.edit'))
                                    <a href="{{ route('admin.users.edit', $user) }}" class="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
                                        Изменить
                                    </a>
                                @endif
                                @if (auth()->user()?->hasPermission('users.delete') && ! auth()->user()->is($user))
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-red-200 px-3 py-1.5 text-red-700 hover:bg-red-50">
                                            Удалить
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500">Пользователей пока нет.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
