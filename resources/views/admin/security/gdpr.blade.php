@extends('admin.layouts.app')

@section('title', 'Настройки GDPR')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Настройки GDPR (Cookie Banner)</h1>

    @if(session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('admin.security.gdpr.update') }}" method="POST" class="bg-white shadow rounded-lg p-6">
        @csrf

        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="enabled" value="1" {{ $settings->enabled ? 'checked' : '' }} 
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-gray-700">Включить баннер cookie</span>
            </label>
        </div>

        <div class="mb-4">
            <label for="banner_title" class="block text-sm font-medium text-gray-700 mb-1">Заголовок баннера</label>
            <input type="text" name="banner_title" id="banner_title" 
                   value="{{ old('banner_title', $settings->banner_title) }}"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('banner_title')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="banner_message" class="block text-sm font-medium text-gray-700 mb-1">Текст сообщения</label>
            <textarea name="banner_message" id="banner_message" rows="4"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('banner_message', $settings->banner_message) }}</textarea>
            @error('banner_message')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label for="accept_button_text" class="block text-sm font-medium text-gray-700 mb-1">Текст кнопки "Принять"</label>
                <input type="text" name="accept_button_text" id="accept_button_text" 
                       value="{{ old('accept_button_text', $settings->accept_button_text) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('accept_button_text')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="decline_button_text" class="block text-sm font-medium text-gray-700 mb-1">Текст кнопки "Отклонить"</label>
                <input type="text" name="decline_button_text" id="decline_button_text" 
                       value="{{ old('decline_button_text', $settings->decline_button_text) }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('decline_button_text')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label for="policy_link" class="block text-sm font-medium text-gray-700 mb-1">Ссылка на политику конфиденциальности</label>
            <input type="url" name="policy_link" id="policy_link" 
                   value="{{ old('policy_link', $settings->policy_link) }}"
                   placeholder="https://example.com/privacy-policy"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('policy_link')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="cookie_duration_days" class="block text-sm font-medium text-gray-700 mb-1">Срок действия cookie (дней)</label>
            <input type="number" name="cookie_duration_days" id="cookie_duration_days" 
                   value="{{ old('cookie_duration_days', $settings->cookie_duration_days) }}"
                   min="1" max="730"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('cookie_duration_days')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                Сохранить настройки
            </button>
        </div>
    </form>

    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Предварительный просмотр</h2>
        <div class="border rounded-lg p-4 bg-white">
            <div class="fixed bottom-0 left-0 right-0 bg-gray-800 text-white p-4">
                <p class="font-semibold mb-2">{{ $settings->banner_title }}</p>
                <p class="text-sm mb-3">{{ $settings->banner_message }}</p>
                <div class="flex gap-2">
                    <button class="bg-green-600 text-white px-4 py-2 rounded text-sm">{{ $settings->accept_button_text }}</button>
                    <button class="bg-gray-600 text-white px-4 py-2 rounded text-sm">{{ $settings->decline_button_text }}</button>
                </div>
            </div>
            <div class="h-32"></div>
        </div>
    </div>
</div>
@endsection
