<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование страницы - VertexCMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950">
    <main class="mx-auto max-w-4xl px-6 py-8">
        <header class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="{{ route('admin.pages.index') }}" class="text-sm text-slate-600 hover:text-slate-950">Назад к страницам</a>
                <h1 class="mt-2 text-2xl font-semibold">Редактирование страницы</h1>
            </div>
            <a href="{{ route('admin.pages.builder', $page) }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-white">
                Открыть Builder
            </a>
        </header>

        @if (session('status'))
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-5 rounded-lg border border-slate-200 bg-white p-6">
            @csrf
            @method('PUT')
            @include('admin.pages.partials.form')
        </form>
    </main>
</body>
</html>

