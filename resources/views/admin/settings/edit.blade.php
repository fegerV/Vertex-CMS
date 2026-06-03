@extends('admin.layouts.app')

@section('title', __('settings.title').' - VertexCMS')
@section('page_title', __('settings.title'))
@section('page_subtitle', __('settings.subtitle'))

@section('content')
    <div class="mx-auto max-w-6xl">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            @foreach ($groups as $groupKey => $group)
                <section class="rounded-lg border border-slate-200 bg-white p-6">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold">{{ $group['label'] }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $group['description'] }}</p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ($group['fields'] as $key => $field)
                            @php
                                $segments = explode('.', $key);
                                $inputName = 'settings['.implode('][', $segments).']';
                                $oldKey = 'settings.'.implode('.', $segments);
                            @endphp
                            <label class="block {{ in_array($field['input'], ['textarea'], true) ? 'md:col-span-2' : '' }}">
                                <span class="mb-1 block text-sm font-medium">{{ $field['label'] }}</span>

                                @if ($field['input'] === 'textarea')
                                    <textarea
                                        name="{{ $inputName }}"
                                        rows="4"
                                        class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
                                    >{{ old($oldKey, $values[$key] ?? '') }}</textarea>
                                @elseif ($field['input'] === 'checkbox')
                                    <span class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2">
                                        <input
                                            type="checkbox"
                                            name="{{ $inputName }}"
                                            value="1"
                                            @checked(old($oldKey, $values[$key] ?? false))
                                            class="rounded border-slate-300"
                                        >
                                        <span class="text-sm text-slate-700">{{ __('settings.enabled') }}</span>
                                    </span>
                                @elseif ($field['input'] === 'select')
                                    <select name="{{ $inputName }}" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
                                        @foreach ($field['options'] as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" @selected(old($oldKey, $values[$key] ?? '') == $optionValue)>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        type="{{ $field['input'] }}"
                                        name="{{ $inputName }}"
                                        value="{{ $field['secret'] ?? false ? '' : old($oldKey, $values[$key] ?? '') }}"
                                        @if ($field['secret'] ?? false) placeholder="{{ ! empty($values[$key]) ? __('settings.secret_placeholder') : __('settings.secret_empty') }}" @endif
                                        class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
                                    >
                                @endif

                                @error($key)
                                    <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                                @enderror
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="flex justify-end">
                <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    {{ __('settings.save') }}
                </button>
            </div>
        </form>
    </div>
@endsection
