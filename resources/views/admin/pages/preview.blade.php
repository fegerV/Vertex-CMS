<!DOCTYPE html>
<html lang="{{ config_value('site.admin_locale', 'ru') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>UX Preview - {{ $page->title }}</title>
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="vc-admin-body">
    <div class="mx-auto min-h-screen max-w-[1440px] px-4 py-6 sm:px-6">
        <header class="vc-toolbar mb-6">
            <div class="vc-toolbar-meta">
                <span class="vc-toolbar-title">UX Preview: {{ $page->title }}</span>
                <span class="vc-toolbar-text">Предпросмотр текущего сохранённого состояния страницы без зависимости от публичного статуса.</span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.pages.edit', $page) }}" class="vc-button vc-button-secondary px-4 py-3">Вернуться в редактор</a>
                <a href="{{ route('admin.pages.builder', $page) }}" class="vc-button vc-button-secondary px-4 py-3">Открыть Builder</a>
                @if ($page->getPublicUrl())
                    <a href="{{ $page->getPublicUrl() }}" target="_blank" rel="noopener" class="vc-button vc-button-secondary px-4 py-3">Публичный URL</a>
                @endif
            </div>
        </header>

        <main class="vc-form-surface">
            {!! $renderedHtml !!}
        </main>
    </div>
</body>
</html>
