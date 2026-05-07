<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страницы - VertexCMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950">
    <main class="mx-auto max-w-6xl px-6 py-8">
        <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
                <h1 class="text-2xl font-semibold">Страницы</h1>
            </div>
            <a href="{{ route('admin.pages.create') }}" class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Создать страницу
            </a>
        </header>

        @if (session('status'))
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="w-full border-collapse text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Название</th>
                        <th class="px-4 py-3 font-medium">URI</th>
                        <th class="px-4 py-3 font-medium">Статус</th>
                        <th class="px-4 py-3 font-medium">Обновлено</th>
                        <th class="px-4 py-3 text-right font-medium">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-medium">{{ $page->title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $page->uri }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                    {{ $page->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $page->updated_at?->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
                                        Изменить
                                    </a>
                                    <a href="{{ route('admin.pages.builder', $page) }}" class="rounded-md border border-slate-300 px-3 py-1.5 hover:bg-slate-50">
                                        Builder
                                    </a>
                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-red-200 px-3 py-1.5 text-red-700 hover:bg-red-50">
                                            Удалить
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                Страниц пока нет.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="mt-4">
            {{ $pages->links() }}
        </div>
    </main>
</body>
</html>

