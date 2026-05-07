@extends('admin.layouts.app')

@section('title', 'Система - VertexCMS')
@section('page_title', 'Системная информация')
@section('page_subtitle', 'Окружение, права директорий и установленные модули')

@section('content')
    @if (! $info['storage_writable'] || ! $info['cache_writable'] || ! $info['uploads_writable'])
        <div class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Есть директории без прав на запись. Установка и загрузка файлов могут работать некорректно.
        </div>
    @endif

    <section class="grid gap-4 md:grid-cols-2">
        @foreach ($info as $key => $value)
            @continue($key === 'installed_modules')
            <article class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-sm font-medium text-slate-500">{{ $key }}</p>
                <p class="mt-2 break-words text-lg">
                    @if (is_bool($value))
                        {{ $value ? 'Да' : 'Нет' }}
                    @else
                        {{ $value ?? 'Недоступно' }}
                    @endif
                </p>
            </article>
        @endforeach
    </section>

    <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5">
        <h2 class="text-lg font-semibold">Установленные модули</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="border-b border-slate-100 py-2 font-medium">Name</th>
                        <th class="border-b border-slate-100 py-2 font-medium">Slug</th>
                        <th class="border-b border-slate-100 py-2 font-medium">Version</th>
                        <th class="border-b border-slate-100 py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($info['installed_modules'] as $module)
                        <tr>
                            <td class="border-b border-slate-100 py-2">{{ $module['name'] }}</td>
                            <td class="border-b border-slate-100 py-2">{{ $module['slug'] }}</td>
                            <td class="border-b border-slate-100 py-2">{{ $module['version'] }}</td>
                            <td class="border-b border-slate-100 py-2">{{ $module['status'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-500">Модули пока не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

