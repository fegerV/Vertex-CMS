<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Система - VertexCMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950">
    <main class="mx-auto max-w-6xl px-6 py-8">
        <header class="mb-6">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
            <h1 class="text-2xl font-semibold">Системная информация</h1>
        </header>

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
    </main>
</body>
</html>

