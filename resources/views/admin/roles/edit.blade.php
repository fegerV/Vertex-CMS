@extends('admin.layouts.app')

@section('title', 'Редактирование роли - VertexCMS')
@section('page_title', 'Редактирование роли')
@section('page_subtitle', $role->slug)

@section('content')
    @php($isSuperAdminRole = $role->slug === 'super-admin')

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="vc-toolbar vc-toolbar-sticky">
            <div class="vc-toolbar-meta">
                <a href="{{ route('admin.roles.index') }}" class="text-sm text-[var(--vc-text-soft)] transition hover:text-[var(--vc-text)]">Назад к ролям</a>
                <span class="vc-toolbar-title">{{ $role->name }}</span>
                <span class="vc-toolbar-text">Настройте отображаемое название роли и список разрешений для этой группы пользователей.</span>
            </div>

            <button form="role-form" class="vc-button vc-button-primary vc-button-large" type="submit">
                Сохранить роль
            </button>
        </div>

        <form id="role-form" method="POST" action="{{ route('admin.roles.update', $role) }}" class="vc-form-surface space-y-6">
            @csrf
            @method('PUT')

            <section class="vc-panel vc-panel-muted p-5 vc-form-section">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Основные данные</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Отображаемое название помогает отличать роли в админке и при назначении пользователям.</p>
                </div>

                <label class="vc-field max-w-xl">
                    <span class="vc-field-label">Название</span>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="vc-input">
                    @error('name') <span class="vc-field-error">{{ $message }}</span> @enderror
                </label>
            </section>

            @if ($isSuperAdminRole)
                <div class="rounded-2xl border border-sky-200 bg-sky-50/80 px-4 py-3 text-sm text-sky-900">
                    Роль `super-admin` всегда получает полный набор разрешений. Изменения чекбоксов в этом интерфейсе не применяются.
                </div>
            @endif

            <section class="vc-panel vc-panel-muted p-5 vc-form-section">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Разрешения</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Группы ниже соответствуют основным областям системы: пользователи, страницы, медиа, настройки, AI и так далее.</p>
                </div>

                <div class="space-y-5">
                    @foreach ($permissions as $group => $items)
                        <div>
                            <h3 class="text-base font-semibold text-[var(--vc-text)]">{{ $group ?: 'other' }}</h3>
                            <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                                @foreach ($items as $permission)
                                    <label class="vc-checkbox-row text-sm">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->id }}"
                                            @checked(in_array($permission->id, old('permissions', $selectedPermissions), true))
                                            @disabled($isSuperAdminRole)
                                            class="rounded border-slate-300"
                                        >
                                        <span class="text-[var(--vc-text)]">{{ $permission->slug }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="flex justify-end">
                <button class="vc-button vc-button-primary" type="submit">
                    Сохранить роль
                </button>
            </div>
        </form>
    </div>
@endsection
