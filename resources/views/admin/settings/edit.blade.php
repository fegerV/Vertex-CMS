@extends('admin.layouts.app')

@section('title', 'Настройки - VertexCMS')
@section('page_title', 'Настройки')
@section('page_subtitle', 'Конфигурация сайта, SEO, API, AI, PWA и кеша')

@section('content')
    @php($canEditSettings = auth()->user()?->hasPermission('settings.edit'))

    <div class="mx-auto max-w-6xl">
        @unless ($canEditSettings)
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                У вас есть доступ только на просмотр. Изменение настроек доступно пользователям с permission `settings.edit`.
            </div>
        @endunless

        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <fieldset @disabled(! $canEditSettings) class="space-y-6">
                @foreach ($groups as $groupKey => $group)
                    <section class="vc-panel p-6">
                        <div class="mb-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">{{ $group['label'] }}</h2>
                                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ $group['description'] }}</p>
                                </div>
                                @unless ($canEditSettings)
                                    <span class="vc-badge">
                                        Read only
                                    </span>
                                @endunless
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            @foreach ($group['fields'] as $key => $field)
                                @php
                                    $segments = explode('.', $key);
                                    $inputName = 'settings['.implode('][', $segments).']';
                                    $oldKey = 'settings.'.implode('.', $segments);
                                @endphp
                                <label class="block {{ in_array($field['input'], ['textarea'], true) ? 'md:col-span-2' : '' }}">
                                    <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">{{ $field['label'] }}</span>

                                    @if ($field['input'] === 'textarea')
                                        <textarea
                                            name="{{ $inputName }}"
                                            rows="4"
                                            class="vc-textarea"
                                        >{{ old($oldKey, $values[$key] ?? '') }}</textarea>
                                    @elseif ($field['input'] === 'checkbox')
                                        <span class="vc-checkbox-row">
                                            <input
                                                type="checkbox"
                                                name="{{ $inputName }}"
                                                value="1"
                                                @checked(old($oldKey, $values[$key] ?? false))
                                                class="rounded border-slate-300"
                                            >
                                            <span class="text-sm text-[var(--vc-text-muted)]">Включено</span>
                                        </span>
                                    @elseif ($field['input'] === 'select')
                                        <select name="{{ $inputName }}" class="vc-select">
                                            @foreach ($field['options'] as $optionValue => $optionLabel)
                                                <option value="{{ $optionValue }}" @selected(old($oldKey, $values[$key] ?? '') == $optionValue)>{{ $optionLabel }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input
                                            type="{{ $field['input'] }}"
                                            name="{{ $inputName }}"
                                            value="{{ $field['secret'] ?? false ? '' : old($oldKey, $values[$key] ?? '') }}"
                                            @if ($field['secret'] ?? false) placeholder="{{ ! empty($values[$key]) ? 'Сохранённый ключ скрыт' : 'Введите ключ' }}" @endif
                                            class="vc-input"
                                        >
                                    @endif

                                    @error($key)
                                        <span class="mt-2 block text-sm text-rose-500">{{ $message }}</span>
                                    @enderror
                                </label>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </fieldset>

            @if ($canEditSettings)
                <div class="flex justify-end">
                    <button class="vc-button vc-button-primary px-5 py-3">
                        Сохранить настройки
                    </button>
                </div>
            @endif
        </form>
    </div>
@endsection
