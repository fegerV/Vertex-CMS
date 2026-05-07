<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Медиа - VertexCMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-950">
    <main class="mx-auto max-w-6xl px-6 py-8">
        <header class="mb-6">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-500">VertexCMS</p>
            <h1 class="text-2xl font-semibold">Медиа</h1>
        </header>

        @if (session('status'))
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <section class="mb-6 rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-semibold">Загрузить файл</h2>
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf

                <label class="block md:col-span-2">
                    <span class="mb-1 block text-sm font-medium">Файл</span>
                    <input type="file" name="file" required class="w-full rounded-md border border-slate-300 bg-white px-3 py-2">
                    @error('file')
                        <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Alt</span>
                    <input type="text" name="alt" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
                </label>

                <label class="block">
                    <span class="mb-1 block text-sm font-medium">Title</span>
                    <input type="text" name="title" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900">
                </label>

                <label class="block md:col-span-2">
                    <span class="mb-1 block text-sm font-medium">Caption</span>
                    <textarea name="caption" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 outline-none focus:border-slate-900"></textarea>
                </label>

                <div class="md:col-span-2">
                    <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Загрузить
                    </button>
                </div>
            </form>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($items as $item)
                <article class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <div class="flex aspect-video items-center justify-center bg-slate-100">
                        @if (in_array($item->extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true))
                            <img src="{{ $item->url }}" alt="{{ $item->alt }}" class="h-full w-full object-cover" loading="lazy">
                        @else
                            <span class="text-sm font-medium uppercase text-slate-500">{{ $item->extension }}</span>
                        @endif
                    </div>

                    <div class="space-y-4 p-4">
                        <div>
                            <p class="truncate font-medium">{{ $item->original_filename }}</p>
                            <p class="text-sm text-slate-500">ID {{ $item->id }} · {{ number_format($item->size / 1024, 1) }} KB</p>
                        </div>

                        <form method="POST" action="{{ route('admin.media.update', $item) }}" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <input type="text" name="alt" value="{{ $item->alt }}" placeholder="Alt" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-900">
                            <input type="text" name="title" value="{{ $item->title }}" placeholder="Title" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-900">
                            <textarea name="caption" rows="2" placeholder="Caption" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-900">{{ $item->caption }}</textarea>

                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ $item->url }}" target="_blank" class="text-sm text-slate-600 hover:text-slate-950">Открыть</a>
                                <button class="rounded-md border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-50">
                                    Сохранить
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}">
                            @csrf
                            @method('DELETE')
                            <button class="w-full rounded-md border border-red-200 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50">
                                Удалить
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">
                    Файлов пока нет.
                </div>
            @endforelse
        </section>

        <div class="mt-6">
            {{ $items->links() }}
        </div>
    </main>
</body>
</html>
