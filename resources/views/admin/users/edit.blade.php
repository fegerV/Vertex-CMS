@extends('admin.layouts.app')

@section('title', 'Редактирование пользователя - VertexCMS')
@section('page_title', 'Редактирование пользователя')
@section('page_subtitle', $user->email)

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="vc-toolbar vc-toolbar-sticky">
            <div class="vc-toolbar-meta">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Назад к списку пользователей</a>
                <span class="vc-toolbar-title">{{ $user->name }}</span>
                <span class="vc-toolbar-text">Обновите контактные данные, роли и статус аккаунта. Чтобы оставить пароль прежним, не заполняйте пароль повторно.</span>
            </div>

            <button form="user-form" class="vc-button vc-button-primary vc-button-large" type="submit">
                Сохранить пользователя
            </button>
        </div>

        <form id="user-form" method="POST" action="{{ route('admin.users.update', $user) }}" class="vc-form-surface space-y-6">
            @csrf
            @method('PUT')
            @include('admin.users.partials.form')
        </form>
    </div>
@endsection
