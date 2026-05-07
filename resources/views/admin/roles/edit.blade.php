@extends('admin.layouts.app')

@section('title', 'Редактирование роли - VertexCMS')
@section('page_title', 'Редактирование роли')
@section('page_subtitle', $role->slug)

@section('content')
    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6 rounded-lg border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        <label class="block max-w-xl">
            <span class="mb-1 block text-sm font-medium">Название</span>
            <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
            @error('name') <span class="mt-1 block text-sm text-red-600">{{ $message }}</span> @enderror
        </label>

        <section class="space-y-5">
            @foreach ($permissions as $group => $items)
                <div>
                    <h2 class="text-base font-semibold">{{ $group ?: 'other' }}</h2>
                    <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($items as $permission)
                            <label class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission->id }}"
                                    @checked(in_array($permission->id, old('permissions', $selectedPermissions), true))
                                    class="rounded border-slate-300"
                                >
                                <span>{{ $permission->slug }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </section>

        <div class="flex justify-end border-t border-slate-100 pt-5">
            <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Сохранить
            </button>
        </div>
    </form>
@endsection
