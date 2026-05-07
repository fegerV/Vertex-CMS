<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логи - VertexCMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950">
    <main class="mx-auto max-w-6xl px-6 py-8">
        <header class="mb-6">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
            <h1 class="text-2xl font-semibold">Логи действий</h1>
        </header>

        <form method="GET" action="{{ route('admin.system.logs') }}" class="mb-6 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 md:grid-cols-3">
            <input
                type="text"
                name="action"
                value="{{ $filters['action'] ?? '' }}"
                placeholder="Action"
                class="rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
            >
            <input
                type="number"
                name="user_id"
                value="{{ $filters['user_id'] ?? '' }}"
                placeholder="User ID"
                class="rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"
            >
            <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                Фильтровать
            </button>
        </form>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <table class="w-full border-collapse text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Дата</th>
                        <th class="px-4 py-3 font-medium">User</th>
                        <th class="px-4 py-3 font-medium">Action</th>
                        <th class="px-4 py-3 font-medium">Entity</th>
                        <th class="px-4 py-3 font-medium">IP</th>
                        <th class="px-4 py-3 font-medium">Описание</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 text-slate-600">{{ $log->created_at?->format('d.m.Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $log->user_id ?? '-' }}</td>
                            <td class="px-4 py-3 font-medium">{{ $log->action }}</td>
                            <td class="px-4 py-3">{{ $log->entity_type }} {{ $log->entity_id }}</td>
                            <td class="px-4 py-3">{{ $log->ip }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">Логи пока не найдены.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </main>
</body>
</html>

