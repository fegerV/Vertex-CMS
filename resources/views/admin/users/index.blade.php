@extends('admin.layouts.app')

@section('title', 'Пользователи - VertexCMS')
@section('page_title', 'Пользователи')
@section('page_subtitle', 'Управление аккаунтами и ролями')

@section('content')
    @if (auth()->user()?->hasPermission('users.create'))
        <div class="mb-6 flex justify-end">
            <a href="{{ route('admin.users.create') }}" class="vc-button vc-button-primary px-4 py-3">
                Создать пользователя
            </a>
        </div>
    @endif

    <section class="vc-table-wrap">
        <table class="vc-table text-sm">
            <thead>
                <tr>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Статус</th>
                    <th>Роли</th>
                    <th class="text-right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="font-medium text-[var(--vc-text)]">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="vc-badge">{{ $user->status }}</span></td>
                        <td>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                        <td>
                            <div class="flex justify-end gap-2">
                                @if (auth()->user()?->hasPermission('users.edit'))
                                    <a href="{{ route('admin.users.edit', $user) }}" class="vc-button vc-button-secondary px-3 py-2">
                                        Изменить
                                    </a>
                                @endif
                                @if (auth()->user()?->hasPermission('users.delete') && ! auth()->user()->is($user))
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="vc-button vc-button-danger px-3 py-2">
                                            Удалить
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-[var(--vc-text-muted)]">Пользователей пока нет.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
