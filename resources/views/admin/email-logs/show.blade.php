@extends('admin.layouts.app')

@section('title', 'Email Log #'.$log->id.' - VertexCMS')
@section('page_title', 'Детали письма')
@section('page_subtitle', 'Лог #'.$log->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Meta -->
    <section class="vc-panel vc-panel-strong p-5">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-[var(--vc-text-muted)]">Статус</div>
                <div class="mt-1">
                    @if($log->status === 'sent')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 text-green-700">
                            ✅ Отправлено {{ $log->sent_at?->format('d.m.Y H:i') }}
                        </span>
                    @elseif($log->status === 'pending')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-yellow-100 text-yellow-700">
                            ⏳ В очереди
                        </span>
                    @elseif($log->status === 'failed')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-100 text-red-700">
                            ❌ Ошибка {{ $log->failed_at?->format('d.m.Y H:i') }}
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600">{{ $log->status }}</span>
                    @endif
                </div>
            </div>
            <div>
                <div class="text-sm text-[var(--vc-text-muted)]">Шаблон</div>
                <div class="mt-1 font-medium">{{ $log->template_key }}</div>
            </div>
            <div>
                <div class="text-sm text-[var(--vc-text-muted)]">Получатель</div>
                <div class="mt-1">
                    <div>{{ $log->recipient_email }}</div>
                    @if($log->recipient_name)
                        <div class="text-sm">{{ $log->recipient_name }}</div>
                    @endif
                </div>
            </div>
            <div>
                <div class="text-sm text-[var(--vc-text-muted)]">Попыток</div>
                <div class="mt-1">{{ $log->retry_count }}</div>
            </div>
        </div>

        @if($log->error_message)
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
                <strong>Ошибка:</strong> {{ $log->error_message }}
            </div>
        @endif

        @if($log->headers)
            <div class="mt-4">
                <details class="group">
                    <summary class="cursor-pointer text-sm text-[var(--vc-text-muted)]">Заголовки письма</summary>
                    <pre class="mt-2 p-3 bg-[var(--vc-surface-muted)] rounded text-xs overflow-x-auto">{{ json_encode($log->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            </div>
        @endif

        @if($log->template_vars)
            <div class="mt-4">
                <details class="group">
                    <summary class="cursor-pointer text-sm text-[var(--vc-text-muted)]">Переменные шаблона</summary>
                    <pre class="mt-2 p-3 bg-[var(--vc-surface-muted)] rounded text-xs overflow-x-auto">{{ json_encode($log->template_vars, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            </div>
        @endif
    </section>

    <!-- Subject -->
    <section class="vc-panel vc-panel-strong p-5">
        <h3 class="font-semibold mb-2">Тема письма</h3>
        <div class="p-3 bg-[var(--vc-surface-muted)] rounded">{{ $log->subject }}</div>
    </section>

    <!-- Body -->
    <section class="vc-panel vc-panel-strong p-5">
        <h3 class="font-semibold mb-2">Текст письма</h3>
        <div class="border border-[var(--vc-border)] rounded overflow-hidden">
            <iframe srcdoc="{!! htmlentities($log->body_text ?: strip_tags($log->subject)) !!}" class="w-full h-96" style="border:0"></iframe>
        </div>
    </section>

    <!-- Actions -->
    <div class="flex gap-2">
        <a href="{{ route('admin.email-logs.index') }}" class="vc-button vc-button-secondary">← Назад к логам</a>
        @if($log->status !== 'sent')
            <form method="POST" action="{{ route('admin.email-logs.resend', $log) }}" onsubmit="return confirm('Повторить отправку?')">
                @csrf
                <button class="vc-button vc-button-primary">🔄 Повторить отправку</button>
            </form>
        @endif
        <form method="POST" action="{{ route('admin.email-logs.destroy', $log) }}" class="ml-auto" onsubmit="return confirm('Удалить лог?')">
            @csrf
            @method('DELETE')
            <button class="vc-button vc-button-danger">Удалить лог</button>
        </form>
    </div>
</div>
@endsection
