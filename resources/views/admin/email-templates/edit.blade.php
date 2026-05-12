@extends('admin.layouts.app')

@section('title', $template ? 'Edit Email' : 'New Email Template' . ' - VertexCMS')
@section('page_title', $template ? 'Редактировать шаблон' : 'Новый шаблон')
@section('page_subtitle', $template ? 'Изменение шаблона письма' : 'Создание нового шаблона email')

@section('content')
<div id="email-template-editor" class="max-w-5xl mx-auto">
    <form method="POST" action="{{ $template ? route('admin.email-templates.update', $template) : route('admin.email-templates.store') }}">
        @csrf
        @if($template)
            @method('PUT')
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            <!-- Left col -->
            <div class="space-y-4">
                <div class="vc-field">
                    <label class="vc-field-label">Ключ шаблона (латиница, без пробелов)</label>
                    <input type="text" name="key" value="{{ old('key', $template->key ?? '') }}" class="vc-input" required pattern="[a-z0-9_-]+" maxlength="100">
                    <div class="vc-field-hint">Используется в коде: Email::send('welcome', ...)</div>
                    @error('key') <span class="vc-field-error">{{ $message }}</span> @enderror
                </div>

                <div class="vc-field">
                    <label class="vc-field-label">Название (для админки)</label>
                    <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}" class="vc-input" required maxlength="255">
                    @error('name') <span class="vc-field-error">{{ $message }}</span> @enderror
                </div>

                <div class="vc-field">
                    <label class="vc-field-label">Тема письма</label>
                    <input type="text" name="subject" value="{{ old('subject', $template->subject ?? '') }}" class="vc-input" required maxlength="500">
                    @error('subject') <span class="vc-field-error">{{ $message }}</span> @enderror
                </div>

                <div class="vc-field">
                    <label class="vc-field-label">Категория</label>
                    <input type="text" name="category" value="{{ old('category', $template->category ?? 'general') }}" class="vc-input" maxlength="50" list="categories">
                    <datalist id="categories">
                        <option value="transactional">
                        <option value="marketing">
                        <option value="notification">
                        <option value="system">
                    </datalist>
                    @error('category') <span class="vc-field-error">{{ $message }}</span> @enderror
                </div>

                <div class="vc-field">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                        <span>Активен</span>
                    </label>
                </div>
            </div>

            <!-- Right col -->
            <div class="space-y-4">
                <div class="vc-field">
                    <label class="vc-field-label">Переменные по умолчанию (JSON)</label>
                    <textarea name="default_vars" rows="6" class="vc-textarea font-mono text-sm">{{ old('default_vars', $template->default_vars ? json_encode($template->default_vars, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '{\n  "site_name": "VertexCMS"\n}') }}</textarea>
                    <div class="vc-field-hint">
                        Пример: <code>{"user_name":"John","reset_link":"..."}</code><br>
                        Используйте <code>@{{ variable }}</code> в HTML-теле письма
                    </div>
                    @error('default_vars') <span class="vc-field-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- HTML Body -->
        <div class="mt-6">
            <label class="vc-field-label mb-2 block">HTML тело письма ( Blade + <code>@{{ var }}</code> )</label>
            <textarea name="body_html" rows="18" class="vc-textarea font-mono text-sm" required>{{ old('body_html', $template->body_html ?? '') }}</textarea>
            @error('body_html') <span class="vc-field-error">{{ $message }}</span> @enderror

            <div class="mt-2 text-sm text-[var(--vc-text-muted)]">
                Доступные переменные:
                <ul class="list-disc ml-5 mt-1 space-y-1">
                    <li><code>@{{ site_name }}</code> — Название сайта</li>
                    <li><code>@{{ site_url }}</code> — URL сайта</li>
                    <li><code>@{{ user_name }}</code> — Имя пользователя</li>
                    <li><code>@{{ user_email }}</code> — Email пользователя</li>
                    <li>И другие, указанные выше в "Переменные по умолчанию"</li>
                </ul>
            </div>
        </div>

        <!-- Plain Text -->
        <div class="mt-6">
            <label class="vc-field-label mb-2 block">Текстовая версия (опционально)</label>
            <textarea name="body_text" rows="8" class="vc-textarea font-mono text-sm">{{ old('body_text', $template->body_text ?? '') }}</textarea>
            <div class="vc-field-hint">Если не заполнено, будет сгенерировано автоматически из HTML</div>
            @error('body_text') <span class="vc-field-error">{{ $message }}</span> @enderror
        </div>

        <!-- Actions -->
        <div class="mt-8 flex items-center gap-4">
            <button type="submit" class="vc-button vc-button-primary">
                {{ $template ? 'Сохранить' : 'Создать' }}
            </button>
            <a href="{{ route('admin.email-templates.index') }}" class="vc-button vc-button-secondary">Отмена</a>

            @if($template)
                <a href="{{ route('admin.email-templates.preview', $template) }}" target="_blank" class="vc-button vc-button-secondary">
                    👁 Предпросмотр
                </a>
                @can('mail.edit')
                    <form method="POST" action="{{ route('admin.email-templates.send-test', $template) }}" class="inline" onsubmit="return confirm('Отправить тестовое письмо на {{ auth()->user()->email }}?')">
                        @csrf
                        <input type="hidden" name="test_email" value="{{ auth()->user()->email }}">
                        <button type="submit" class="vc-button vc-button-secondary">
                            ✉️ Тестовая отправка
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const { createApp, ref } = Vue;
    createApp({ setup() { return {}; } }).mount('#email-template-editor');
</script>
@endpush
