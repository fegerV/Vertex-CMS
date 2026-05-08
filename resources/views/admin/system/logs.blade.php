@extends('admin.layouts.app')

@section('title', 'Логи - VertexCMS')
@section('page_title', 'Логи действий')
@section('page_subtitle', 'Аудит действий администраторов')

@section('content')
    <form method="GET" action="{{ route('admin.system.logs') }}" class="vc-panel mb-6 grid gap-3 p-4 md:grid-cols-3">
        <input
            type="text"
            name="action"
            value="{{ $filters['action'] ?? '' }}"
            placeholder="Action"
            class="vc-input"
        >
        <input
            type="number"
            name="user_id"
            value="{{ $filters['user_id'] ?? '' }}"
            placeholder="User ID"
            class="vc-input"
        >
        <button class="vc-button vc-button-primary px-4 py-3">
            Фильтровать
        </button>
    </form>

    <section class="vc-table-wrap">
        <table class="vc-table text-sm">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>IP</th>
                    <th>Описание</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at?->format('d.m.Y H:i') }}</td>
                        <td>{{ $log->user_id ?? '-' }}</td>
                        <td class="font-medium text-[var(--vc-text)]">{{ $log->action }}</td>
                        <td>{{ $log->entity_type }} {{ $log->entity_id }}</td>
                        <td>{{ $log->ip }}</td>
                        <td>{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[var(--vc-text-muted)]">Логи пока не найдены.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endsection

