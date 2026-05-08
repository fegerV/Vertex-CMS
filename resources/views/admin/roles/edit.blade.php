@extends('admin.layouts.app')

@section('title', 'Редактирование роли - VertexCMS')
@section('page_title', 'Редактирование роли')
@section('page_subtitle', $role->slug)

@section('content')
    @php($isSuperAdminRole = $role->slug === 'super-admin')

    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="vc-panel space-y-6 p-6">
        @csrf
        @method('PUT')

        <label class="block max-w-xl">
            <span class="mb-2 block text-sm font-semibold text-[var(--vc-text)]">Название</span>
            <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="vc-input">
            @error('name') <span class="mt-2 block text-sm text-rose-500">{{ $message }}</span> @enderror
        </label>

        @if ($isSuperAdminRole)
            <div class="rounded-2xl border border-sky-200 bg-sky-50/80 px-4 py-3 text-sm text-sky-900">
                Роль `super-admin` всегда получает полный набор permissions. Изменения чекбоксов в этом интерфейсе не применяются.
            </div>
        @endif

        <section class="space-y-5">
            @foreach ($permissions as $group => $items)
                <div>
                    <h2 class="text-base font-semibold capitalize text-[var(--vc-text)]">{{ $group ?: 'other' }}</h2>
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
        </section>

        <div class="flex justify-end border-t border-[var(--vc-border)] pt-5">
            <button class="vc-button vc-button-primary px-4 py-3">
                Сохранить
            </button>
        </div>
    </form>
@endsection
