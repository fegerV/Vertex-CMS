@extends('admin.layouts.app')

@section('title', 'Медиа - VertexCMS')
@section('page_title', 'Медиа')
@section('page_subtitle', 'Загрузка файлов и управление метаданными')

@section('content')
    @if (auth()->user()?->hasPermission('media.upload'))
        <section class="vc-panel p-5 mb-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-[var(--vc-text)]">Загрузить файл</h2>
                <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Поддерживаются изображения, SVG и PDF до 10 МБ. После загрузки можно сразу заполнить alt, заголовок и подпись.</p>
            </div>

            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="vc-form-grid md:grid-cols-2">
                @csrf

                <label class="vc-field md:col-span-2">
                    <span class="vc-field-label">Файл</span>
                    <span class="vc-field-help">Выберите локальный файл для библиотеки медиа. SVG проходит базовую санитаризацию перед сохранением.</span>
                    <input type="file" name="file" required class="vc-file-input">
                    @error('file')
                        <span class="vc-field-error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="vc-field">
                    <span class="vc-field-label">Alt</span>
                    <input type="text" name="alt" class="vc-input">
                </label>

                <label class="vc-field">
                    <span class="vc-field-label">Заголовок</span>
                    <input type="text" name="title" class="vc-input">
                </label>

                <label class="vc-field md:col-span-2">
                    <span class="vc-field-label">Подпись</span>
                    <textarea name="caption" rows="2" class="vc-textarea"></textarea>
                </label>

                <div class="md:col-span-2">
                    <button class="vc-button vc-button-primary">
                        Загрузить в медиатеку
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($items as $item)
            <article class="overflow-hidden rounded-lg border border-[var(--vc-border)] bg-[var(--vc-surface-strong)]">
                <div class="flex aspect-video items-center justify-center bg-[var(--vc-surface-muted)]">
                    @if (in_array($item->extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true))
                        <img src="{{ $item->url }}" alt="{{ $item->alt }}" class="h-full w-full object-cover" loading="lazy">
                    @else
                        <span class="text-sm font-medium uppercase text-[var(--vc-text-soft)]">{{ $item->extension }}</span>
                    @endif
                </div>

                <div class="space-y-4 p-4">
                    <div>
                        <p class="truncate font-medium text-[var(--vc-text)]">{{ $item->original_filename }}</p>
                        <p class="text-sm text-[var(--vc-text-soft)]">ID {{ $item->id }} · {{ number_format($item->size / 1024, 1) }} KB</p>
                    </div>

                    @if (auth()->user()?->hasPermission('media.edit'))
                        <form method="POST" action="{{ route('admin.media.update', $item) }}" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <input type="text" name="alt" value="{{ $item->alt }}" placeholder="Alt" class="vc-input text-sm">
                            <input type="text" name="title" value="{{ $item->title }}" placeholder="Заголовок" class="vc-input text-sm">
                            <textarea name="caption" rows="2" placeholder="Подпись" class="vc-textarea text-sm">{{ $item->caption }}</textarea>

                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ $item->url }}" target="_blank" class="text-sm text-[var(--vc-text-soft)] hover:text-[var(--vc-text)]">Открыть</a>
                                <button class="vc-button vc-button-secondary">
                                    Сохранить
                                </button>
                            </div>
                        </form>
                    @else
                        <a href="{{ $item->url }}" target="_blank" class="text-sm text-[var(--vc-text-soft)] hover:text-[var(--vc-text)]">Открыть</a>
                    @endif

                    @if (auth()->user()?->hasPermission('media.delete'))
                        <form method="POST" action="{{ route('admin.media.destroy', $item) }}">
                            @csrf
                            @method('DELETE')
                            <button class="vc-button vc-button-danger w-full">
                                Удалить
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="vc-panel p-8 text-center text-[var(--vc-text-soft)] md:col-span-2 xl:col-span-3">
                Файлов пока нет.
            </div>
        @endforelse
    </section>

    <div class="mt-6">
        {{ $items->links() }}
    </div>
@endsection
