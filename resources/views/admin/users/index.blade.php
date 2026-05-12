@extends('admin.layouts.app')

@section('title', 'Пользователи - VertexCMS')
@section('page_title', 'Пользователи')
@section('page_subtitle', 'Управление аккаунтами, ролями и статусами доступа')

@section('content')
    <div class="space-y-6">
        <div class="vc-toolbar">
            <div class="vc-toolbar-meta">
                <span class="vc-toolbar-title">Команда проекта</span>
                <span class="vc-toolbar-text">Здесь настраиваются учётные записи, роли и статусы доступа к админке.</span>
            </div>

            @if (auth()->user()?->hasPermission('users.create'))
                <a href="{{ route('admin.users.create') }}" class="vc-button vc-button-primary">
                    Создать пользователя
                </a>
            @endif
        </div>

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
                            <td><span class="vc-badge">{{ $user->status === 'active' ? 'Активен' : 'Заблокирован' }}</span></td>
                            <td>{{ $user->roles->pluck('name')->join(', ') ?: 'Без роли' }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    @if (auth()->user()?->hasPermission('users.edit'))
                                        <a href="{{ route('admin.users.edit', $user) }}" class="vc-button vc-button-secondary">
                                            Изменить
                                        </a>
                                    @endif
                                    @if (auth()->user()?->hasPermission('users.delete') && ! auth()->user()->is($user))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
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
                            <td colspan="5" class="py-10 text-center text-[var(--vc-text-muted)]">Пользователей пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div>
            {{ $users->links() }}
        </div>
    </div>
@endsection
