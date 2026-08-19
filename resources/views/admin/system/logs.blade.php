@extends('admin.layouts.app')

@section('title', 'Логи действий - VertexCMS')
@section('page_title', 'Логи действий')
@section('page_subtitle', 'Аудит действий администраторов, редакторов и системных операций')

@section('content')
    <div class="space-y-6">
        <div class="vc-toolbar">
            <div class="vc-toolbar-meta">
                <span class="vc-toolbar-title">Журнал событий</span>
                <span class="vc-toolbar-text">Фильтруйте действия по коду операции и пользователю, чтобы быстро находить нужные изменения.</span>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.system.logs') }}" class="vc-panel p-5">
            <div class="vc-form-grid md:grid-cols-[minmax(0,1fr)_220px_auto]">
                <label class="vc-field">
                    <span class="vc-field-label">Код действия</span>
                    <input
                        type="text"
                        name="action"
                        value="{{ $filters['action'] ?? '' }}"
                        placeholder="Например: settings.edit"
                        class="vc-input"
                    >
                </label>

                <label class="vc-field">
                    <span class="vc-field-label">ID пользователя</span>
                    <input
                        type="number"
                        name="user_id"
                        value="{{ $filters['user_id'] ?? '' }}"
                        placeholder="Например: 1"
                        class="vc-input"
                    >
                </label>

                <div class="flex items-end">
                    <button class="vc-button vc-button-primary" type="submit">
                        Применить фильтры
                    </button>
                </div>
            </div>
        </form>

        <section class="vc-table-wrap">
            <table class="vc-table text-sm">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Пользователь</th>
                        <th>Действие</th>
                        <th>Сущность</th>
                        <th>IP</th>
                        <th>Описание</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d.m.Y H:i') }}</td>
                            <td>{{ $log->user_id ?? '—' }}</td>
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

        <div>
            {{ $logs->links() }}
        </div>
    </div>
@endsection
