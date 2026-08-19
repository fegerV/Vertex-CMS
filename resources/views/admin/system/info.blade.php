@extends('admin.layouts.app')

@section('title', 'Система - VertexCMS')
@section('page_title', 'Системная информация')
@section('page_subtitle', 'Окружение, права директорий и установленные модули')

@section('content')
    @if (! $info['storage_writable'] || ! $info['cache_writable'] || ! $info['uploads_writable'])
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-900">
            Есть директории без прав на запись. Установка и загрузка файлов могут работать некорректно.
        </div>
    @endif

    <section class="grid gap-4 md:grid-cols-2">
        @foreach ($info as $key => $value)
            @continue($key === 'installed_modules')
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">{{ $key }}</p>
                <p class="mt-3 break-words text-lg font-semibold text-[var(--vc-text)]">
                    @if (is_bool($value))
                        {{ $value ? 'Да' : 'Нет' }}
                    @else
                        {{ $value ?? 'Недоступно' }}
                    @endif
                </p>
            </article>
        @endforeach
    </section>

    <section class="vc-panel mt-6 p-5">
        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Установленные модули</h2>
        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Список модулей, которые зарегистрированы в системе.</p>
        <div class="mt-4 overflow-x-auto">
            <table class="vc-table text-sm">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Version</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($info['installed_modules'] as $module)
                        <tr>
                            <td class="font-medium text-[var(--vc-text)]">{{ $module['name'] }}</td>
                            <td>{{ $module['slug'] }}</td>
                            <td>{{ $module['version'] }}</td>
                            <td><span class="vc-badge">{{ $module['status'] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-[var(--vc-text-muted)]">Модули пока не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

