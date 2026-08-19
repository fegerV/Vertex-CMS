@extends('admin.layouts.app')

@section('title', 'Создание пользователя - VertexCMS')
@section('page_title', 'Создание пользователя')
@section('page_subtitle', 'Новый аккаунт для администратора, редактора или наблюдателя')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="vc-toolbar vc-toolbar-sticky">
            <div class="vc-toolbar-meta">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Назад к списку пользователей</a>
                <span class="vc-toolbar-title">Новый пользователь</span>
                <span class="vc-toolbar-text">Укажите имя, email, пароль и роли. Пароль обязателен только при создании нового аккаунта.</span>
            </div>

            <button form="user-form" class="vc-button vc-button-primary vc-button-large" type="submit">
                Сохранить пользователя
            </button>
        </div>

        <form id="user-form" method="POST" action="{{ route('admin.users.store') }}" class="vc-form-surface space-y-6">
            @csrf
            @include('admin.users.partials.form')
        </form>
    </div>
@endsection
