@extends('admin.layouts.app')

@section('title', 'Настройки - VertexCMS')
@section('page_title', 'Настройки')
@section('page_subtitle', 'Конфигурация сайта, SEO, API, AI, PWA и кеша')

@push('styles')
<style>
    .settings-accordion-item {
        border: 1px solid var(--vc-border);
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.4);
        overflow: hidden;
        transition: all 200ms ease;
    }
    .settings-accordion-item:hover {
        background: rgba(255, 255, 255, 0.6);
    }
    html[data-theme='dark'] .settings-accordion-item {
        background: rgba(15, 23, 42, 0.3);
    }
    html[data-theme='dark'] .settings-accordion-item:hover {
        background: rgba(15, 23, 42, 0.5);
    }
    .settings-accordion-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        cursor: pointer;
        user-select: none;
        gap: 0.75rem;
    }
    .settings-accordion-header:hover {
        background: rgba(148, 163, 184, 0.08);
    }
    .settings-accordion-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--vc-text);
    }
    .settings-accordion-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 0.5rem;
        background: rgba(45, 212, 191, 0.12);
        color: var(--vc-primary);
        flex-shrink: 0;
    }
    .settings-accordion-chevron {
        width: 1.25rem;
        height: 1.25rem;
        color: var(--vc-text-muted);
        transition: transform 200ms ease;
        flex-shrink: 0;
    }
    .settings-accordion-item.active .settings-accordion-chevron {
        transform: rotate(180deg);
    }
    .settings-accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 250ms ease;
    }
    .settings-accordion-item.active .settings-accordion-content {
        max-height: 2000px;
    }
    .setting-field-card {
        border: 1px solid var(--vc-border);
        border-radius: 0.65rem;
        background: rgba(255, 255, 255, 0.5);
        padding: 0.75rem;
        transition: all 150ms ease;
    }
    .setting-field-card:hover {
        background: rgba(255, 255, 255, 0.7);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    html[data-theme='dark'] .setting-field-card {
        background: rgba(15, 23, 42, 0.4);
    }
    html[data-theme='dark'] .setting-field-card:hover {
        background: rgba(15, 23, 42, 0.6);
    }
    .field-icon-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 0.4rem;
        background: rgba(45, 212, 191, 0.1);
        color: var(--vc-primary);
    }
    @media (max-width: 768px) {
        .vc-form-grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    @php
        $canEditSettings = auth()->user()?->hasPermission('settings.edit');
        $canManageAiKeys = $canManageAiKeys ?? false;
        
        $sectionIcons = [
            'general' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>',
            'seo' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>',
            'api' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>',
            'ai' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>',
            'pwa' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
            'cache' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>',
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-4">
        @unless ($canEditSettings)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>У вас доступ только на просмотр. Изменение настроек доступно пользователям с правом <code class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-xs">settings.edit</code>.</span>
            </div>
        @endunless

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4" x-data="{ activeAccordion: null }">
            @csrf
            @method('PUT')

            <!-- Compact Toolbar -->
            <div class="vc-toolbar vc-toolbar-sticky">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="field-icon-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <span class="vc-toolbar-title text-base">Параметры проекта</span>
                        </div>
                    </div>
                </div>

                @if ($canEditSettings)
                    <button class="vc-button vc-button-primary" type="submit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Сохранить все
                    </button>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 text-slate-600 text-xs font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Только просмотр
                    </span>
                @endif
            </div>

            <fieldset @disabled(! $canEditSettings) class="space-y-3">
                @foreach ($groups as $groupKey => $group)
                    <div class="settings-accordion-item" 
                         x-data="{ isOpen: false }" 
                         @click.away="isOpen = false"
                         :class="{ 'active': isOpen }">
                        <!-- Accordion Header -->
                        <div class="settings-accordion-header" @click="isOpen = !isOpen">
                            <div class="settings-accordion-title">
                                <div class="settings-accordion-icon">
                                    {!! $sectionIcons[$groupKey] ?? $sectionIcons['general'] !!}
                                </div>
                                <span>{{ $group['label'] }}</span>
                            </div>
                            <svg class="settings-accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        
                        <!-- Accordion Content -->
                        <div class="settings-accordion-content">
                            <div class="p-4 pt-0">
                                <div class="vc-form-grid vc-form-grid-2">
                                    @foreach ($group['fields'] as $key => $field)
                                        @php
                                            $segments = explode('.', $key);
                                            $inputName = 'settings['.implode('][', $segments).']';
                                            $oldKey = 'settings.'.implode('.', $segments);
                                            $isSecretField = (bool) ($field['secret'] ?? false);
                                            $isAiSecretField = str_starts_with($key, 'ai.') && $isSecretField;
                                            $fieldDisabled = (! $canEditSettings) || ($isAiSecretField && ! $canManageAiKeys);
                                            $fieldValue = old($oldKey, $values[$key] ?? '');
                                            $fieldInput = $field['input'] ?? 'text';
                                            $fieldType = $field['type'] ?? 'string';
                                            $fieldPlaceholder = '';

                                            if ($isSecretField) {
                                                $fieldPlaceholder = ! empty($values[$key]) ? '••••••••••••' : 'Введите ключ';
                                            }
                                            
                                            $fieldIcons = [
                                                'name' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>',
                                                'url' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>',
                                                'email' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
                                                'key' => '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>',
                                            ];
                                            $fieldIcon = null;
                                            foreach (array_keys($fieldIcons) as $iconKey) {
                                                if (str_contains(strtolower($key), $iconKey)) {
                                                    $fieldIcon = $fieldIcons[$iconKey];
                                                    break;
                                                }
                                            }
                                        @endphp

                                        <label class="setting-field-card {{ $fieldInput === 'textarea' ? 'md:col-span-2' : '' }}">
                                            <div class="flex items-start gap-2 mb-2">
                                                @if ($fieldIcon)
                                                    <div class="field-icon-sm flex-shrink-0">
                                                        {!! $fieldIcon !!}
                                                    </div>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-xs font-semibold text-[var(--vc-text)]">{{ $field['label'] }}</span>
                                                </div>
                                                @if ($isSecretField || $fieldType === 'encrypted')
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-medium flex-shrink-0">
                                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                        Защищено
                                                    </span>
                                                @endif
                                            </div>

                                        @if ($fieldInput === 'textarea')
                                            @php
                                                $displayValue = ($fieldType === 'json' && is_array($fieldValue))
                                                    ? json_encode($fieldValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                                    : $fieldValue;
                                            @endphp
                                            <textarea
                                                name="{{ $inputName }}"
                                                rows="3"
                                                class="vc-textarea text-xs"
                                                placeholder="{{ $fieldPlaceholder }}"
                                                @disabled($fieldDisabled)
                                            >{{ $displayValue }}</textarea>
                                            @elseif ($fieldInput === 'checkbox')
                                                <label class="flex items-center gap-2 p-2 rounded border border-var(--vc-border) hover:bg-white/50 transition-colors cursor-pointer">
                                                    <input
                                                        type="checkbox"
                                                        name="{{ $inputName }}"
                                                        value="1"
                                                        @checked((bool) $fieldValue)
                                                        class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500/20"
                                                        @disabled($fieldDisabled)
                                                    >
                                                    <span class="text-xs text-[var(--vc-text-muted)]">Включено</span>
                                                </label>
                                            @elseif ($fieldInput === 'select')
                                                <select name="{{ $inputName }}" class="vc-select text-xs" @disabled($fieldDisabled)>
                                                    @foreach ($field['options'] as $optionValue => $optionLabel)
                                                        <option value="{{ $optionValue }}" @selected((string) $fieldValue === (string) $optionValue)>{{ $optionLabel }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input
                                                    type="{{ $fieldInput }}"
                                                    name="{{ $inputName }}"
                                                    value="{{ $isSecretField ? '' : $fieldValue }}"
                                                    placeholder="{{ $fieldPlaceholder }}"
                                                    class="vc-input text-xs"
                                                    @disabled($fieldDisabled)
                                                >
                                            @endif

                                            @if ($fieldType === 'encrypted')
                                                <p class="mt-1.5 text-[10px] text-[var(--vc-text-soft)] flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Значение хранится в зашифрованном виде
                                                </p>
                                            @elseif ($fieldInput === 'checkbox')
                                                <p class="mt-1.5 text-[10px] text-[var(--vc-text-soft)]">Отключите опцию, если она не должна использоваться по умолчанию.</p>
                                            @endif

                                            @if ($isAiSecretField && ! $canManageAiKeys)
                                                <p class="mt-1.5 text-[10px] text-amber-600 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                    Редактирование AI-ключей доступно только пользователям с правом <code>ai.manage_keys</code>
                                                </p>
                                            @endif

                                            @error($key)
                                                <p class="mt-1.5 text-[10px] text-red-600 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </fieldset>

            @if ($canEditSettings)
                <div class="vc-toolbar mt-4">
                    <div class="vc-toolbar-meta">
                        <span class="vc-toolbar-title text-sm">Готово?</span>
                        <span class="vc-toolbar-text text-xs">Проверьте все изменения перед сохранением.</span>
                    </div>
                    <button class="vc-button vc-button-primary" type="submit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Сохранить настройки
                    </button>
                </div>
            @endif
        </form>
    </div>
@endsection
