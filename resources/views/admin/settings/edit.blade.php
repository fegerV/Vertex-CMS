@extends('admin.layouts.app')

@section('title', 'Настройки - VertexCMS')
@section('page_title', 'Настройки')
@section('page_subtitle', 'Конфигурация сайта, SEO, API, AI, PWA и кеша')

@section('content')
    @php
        $canEditSettings = auth()->user()?->hasPermission('settings.edit');
        $canManageAiKeys = $canManageAiKeys ?? false;
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">
        @unless ($canEditSettings)
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                У вас доступ только на просмотр. Изменение настроек доступно пользователям с правом `settings.edit`.
            </div>
        @endunless

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="vc-toolbar vc-toolbar-sticky">
                <div class="vc-toolbar-meta">
                    <span class="vc-toolbar-title">Параметры проекта</span>
                    <span class="vc-toolbar-text">Сохраняйте базовые настройки сайта, API, AI и PWA в одном месте.</span>
                </div>

                @if ($canEditSettings)
                    <button class="vc-button vc-button-primary vc-button-large" type="submit">
                        Сохранить настройки
                    </button>
                @else
                    <span class="vc-badge">Только просмотр</span>
                @endif
            </div>

            <fieldset @disabled(! $canEditSettings) class="space-y-6">
                @foreach ($groups as $group)
                    <section class="vc-panel p-6">
                        <div class="mb-5 flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-[var(--vc-text)]">{{ $group['label'] }}</h2>
                                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ $group['description'] }}</p>
                            </div>

                            @unless ($canEditSettings)
                                <span class="vc-badge">Только просмотр</span>
                            @endunless
                        </div>

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
                                        $fieldPlaceholder = ! empty($values[$key]) ? 'Сохранённый ключ скрыт' : 'Введите ключ';
                                    }
                                @endphp

                                <label class="vc-field {{ $fieldInput === 'textarea' ? 'md:col-span-2' : '' }}">
                                    <span class="vc-field-label">{{ $field['label'] }}</span>

                                    @if ($fieldInput === 'textarea')
                                        <textarea
                                            name="{{ $inputName }}"
                                            rows="4"
                                            class="vc-textarea"
                                            @disabled($fieldDisabled)
                                        >{{ $fieldValue }}</textarea>
                                    @elseif ($fieldInput === 'checkbox')
                                        <span class="vc-checkbox-row">
                                            <input
                                                type="checkbox"
                                                name="{{ $inputName }}"
                                                value="1"
                                                @checked((bool) $fieldValue)
                                                class="rounded border-slate-300"
                                                @disabled($fieldDisabled)
                                            >
                                            <span class="text-sm text-[var(--vc-text-muted)]">Включено</span>
                                        </span>
                                    @elseif ($fieldInput === 'select')
                                        <select name="{{ $inputName }}" class="vc-select" @disabled($fieldDisabled)>
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
                                            class="vc-input"
                                            @disabled($fieldDisabled)
                                        >
                                    @endif

                                    @if ($fieldType === 'encrypted')
                                        <span class="vc-field-help">
                                            Значение хранится в зашифрованном виде и не показывается в интерфейсе после сохранения.
                                        </span>
                                    @elseif ($fieldInput === 'checkbox')
                                        <span class="vc-field-help">
                                            Отключите опцию, если она не должна использоваться на сайте по умолчанию.
                                        </span>
                                    @endif

                                    @if ($isAiSecretField && ! $canManageAiKeys)
                                        <span class="vc-field-help">
                                            Редактирование AI-ключей доступно только пользователям с правом `ai.manage_keys`.
                                        </span>
                                    @endif

                                    @error($key)
                                        <span class="vc-field-error">{{ $message }}</span>
                                    @enderror
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </fieldset>

            @if ($canEditSettings)
                <div class="flex justify-end">
                    <button class="vc-button vc-button-primary" type="submit">
                        Сохранить настройки
                    </button>
                </div>
            @endif
        </form>
    </div>
@endsection
