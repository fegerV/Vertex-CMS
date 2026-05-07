<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteName }} - Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950">
    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div>
                    <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
                    <h1 class="text-xl font-semibold">{{ $siteName }}</h1>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="rounded-md border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50">
                        Выйти
                    </button>
                </form>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-8">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-sm text-slate-500">Страницы</p>
                    <strong class="mt-2 block text-3xl">{{ $stats['pages'] }}</strong>
                </article>
                <article class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-sm text-slate-500">Опубликовано</p>
                    <strong class="mt-2 block text-3xl">{{ $stats['published_pages'] }}</strong>
                </article>
                <article class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-sm text-slate-500">Черновики</p>
                    <strong class="mt-2 block text-3xl">{{ $stats['draft_pages'] }}</strong>
                </article>
                <article class="rounded-lg border border-slate-200 bg-white p-4">
                    <p class="text-sm text-slate-500">Медиа</p>
                    <strong class="mt-2 block text-3xl">{{ $stats['media_files'] }}</strong>
                </article>
            </section>

            <section class="mt-8 rounded-lg border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Последние действия</h2>

                <div class="mt-4 space-y-3">
                    @forelse ($recentActivity as $activity)
                        <div class="border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                            <p class="font-medium">{{ $activity->action }}</p>
                            <p class="text-sm text-slate-500">{{ $activity->description }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Действий пока нет.</p>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</body>
</html>
