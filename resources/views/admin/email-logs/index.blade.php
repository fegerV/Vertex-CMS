@extends('admin.layouts.app')

@section('title', 'Email Logs - VertexCMS')
@section('page_title', 'Логи писем')
@section('page_subtitle', 'История отправленных писем')

@section('content')
<div class="space-y-4">
    <!-- Filters -->
    <section class="vc-panel vc-panel-strong p-4">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" v-model="search" placeholder="Поиск по email или теме..." class="vc-input w-full">
            </div>
            <select v-model="filterTemplate" class="vc-input w-auto">
                <option value="">Все шаблоны</option>
                <option v-for="(count, key) in templates" :value="key">@{{ key }} (@{{ count }})</option>
            </select>
            <select v-model="filterStatus" class="vc-input w-auto">
                <option value="">Все статусы</option>
                <option v-for="(label, value) in statusLabels" :value="value">@{{ label }}</option>
            </select>
        </div>
    </section>

    <!-- Table -->
    <section class="vc-panel overflow-hidden">
        <table class="w-full border-collapse text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Дата</th>
                    <th class="px-4 py-3 text-left font-medium">Получатель</th>
                    <th class="px-4 py-3 text-left font-medium">Тема</th>
                    <th class="px-4 py-3 text-left font-medium">Шаблон</th>
                    <th class="px-4 py-3 text-left font-medium">Статус</th>
                    <th class="px-4 py-3 text-left font-medium">Ошибка</th>
                    <th class="px-4 py-3 text-right font-medium">Действия</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--vc-border)]">
                @forelse ($logs as $log)
                    <tr class="hover:bg-[var(--vc-surface-muted)] transition-colors">
                        <td class="px-4 py-3 text-[var(--vc-text-soft)]">
                            {{ $log->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $log->recipient_email }}</div>
                            @if($log->recipient_name)
                                <div class="text-xs text-[var(--vc-text-muted)]">{{ $log->recipient_name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 truncate max-w-xs" title="{{ $log->subject }}">
                            {{ \Illuminate\Support\Str::limit($log->subject, 60) }}
                        </td>
                        <td class="px-4 py-3">
                            <code class="bg-[var(--vc-surface-muted)] px-2 py-1 rounded text-xs">{{ $log->template_key }}</code>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                                @if($log->status === 'sent') bg-green-100 text-green-700
                                @elseif($log->status === 'pending') bg-yellow-100 text-yellow-700
                                @elseif($log->status === 'failed') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-600 @endif">
                                @if($log->status === 'sent') ✅ Отправлено
                                @elseif($log->status === 'pending') ⏳ В очереди
                                @elseif($log->status === 'failed') ❌ Ошибка
                                @else ⚠️ {{ $log->status }} @endif
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-red-600 max-w-xs truncate" title="{{ $log->error_message }}">
                            {{ $log->error_message ? \Illuminate\Support\Str::limit($log->error_message, 50) : '' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @can('mail.view')
                                    <a href="{{ route('admin.email-logs.show', $log) }}" class="vc-button vc-button-secondary vc-button-sm" title="Детали">
                                📋
                                    </a>
                                @endcan
                                @if($log->status !== 'sent')
                                    @can('mail.edit')
                                        <form method="POST" action="{{ route('admin.email-logs.resend', $log) }}" class="inline" onsubmit="return confirm('Повторить отправку?')">
                                            @csrf
                                            <button class="vc-button vc-button-secondary vc-button-sm" title="Повторить">
                                                🔄
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                                @can('mail.delete')
                                    <form method="POST" action="{{ route('admin.email-logs.destroy', $log) }}" class="inline" onsubmit="return confirm('Удалить лог?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="vc-button vc-button-danger vc-button-sm" title="Удалить">
                                            🗑
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-[var(--vc-text-muted)]">
                            Логов пока нет
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    const { createApp, ref } = Vue;
    createApp({
        setup() {
            const search = ref('');
            const filterTemplate = ref('');
            const filterStatus = ref('');
            const templates = @json($templates ?? []);
            const statusLabels = @json([
                'pending' => 'В очереди',
                'sent' => 'Отправлено',
                'failed' => 'Ошибка',
                'bounced' => 'Возвращено',
            ]);

            return { search, filterTemplate, filterStatus, templates, statusLabels };
        },
    }).mount('div[data-app]');
</script>
@endpush
