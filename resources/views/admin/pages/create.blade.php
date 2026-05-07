<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание страницы - VertexCMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950">
    <main class="mx-auto max-w-4xl px-6 py-8">
        <header class="mb-6">
            <a href="{{ route('admin.pages.index') }}" class="text-sm text-slate-600 hover:text-slate-950">Назад к страницам</a>
            <h1 class="mt-2 text-2xl font-semibold">Создание страницы</h1>
        </header>

        <form method="POST" action="{{ route('admin.pages.store') }}" class="space-y-5 rounded-lg border border-slate-200 bg-white p-6">
            @csrf
            @include('admin.pages.partials.form')
        </form>
    </main>
</body>
</html>

