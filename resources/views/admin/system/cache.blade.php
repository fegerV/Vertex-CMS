<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кеш - VertexCMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950">
    <main class="mx-auto max-w-4xl px-6 py-8">
        <header class="mb-6">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
            <h1 class="text-2xl font-semibold">Кеш</h1>
        </header>

        @if (session('status'))
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-2">
            @foreach ($status as $key => $value)
                <article class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-sm font-medium text-slate-500">{{ $key }}</p>
                    <p class="mt-2 break-words text-lg">
                        @if (is_bool($value))
                            {{ $value ? 'Да' : 'Нет' }}
                        @else
                            {{ $value }}
                        @endif
                    </p>
                </article>
            @endforeach
        </section>

        <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-semibold">Очистка кеша</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach (['all' => 'Весь кеш', 'application' => 'Кеш приложения', 'pages' => 'Кеш страниц'] as $scope => $label)
                    <form method="POST" action="{{ route('admin.system.cache.clear') }}">
                        @csrf
                        <input type="hidden" name="scope" value="{{ $scope }}">
                        <button class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    </main>
</body>
</html>
