@php
    $seo = $page?->seoMeta;
    $title = $seo?->title ?: ($page?->title ?? 'VertexCMS');
    $description = $seo?->description ?: config_value('seo.default_description', '');
    $canonical = $seo?->canonical_url ?: url($page?->uri ?? '/');
    $robots = $seo?->robots ?: 'index, follow';
    $ogTitle = $seo?->og_title ?: $title;
    $ogDescription = $seo?->og_description ?: $description;
@endphp
<!DOCTYPE html>
<html lang="{{ config_value('site.locale', 'ru') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @if (config_value('pwa.enabled', false))
        <link rel="manifest" href="{{ route('frontend.manifest') }}">
        <meta name="theme-color" content="{{ config_value('pwa.theme_color', '#020617') }}">
    @endif
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    <meta name="robots" content="{{ $robots }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    @if ($ogDescription)
        <meta property="og:description" content="{{ $ogDescription }}">
    @endif
    @if ($seo?->ogImage)
        <meta property="og:image" content="{{ $seo->ogImage->url }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical }}">
    @if ($seo?->schema_json)
        <script type="application/ld+json">
            {!! json_encode($seo->schema_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-white text-slate-950">
    <main class="vc-page">
        @if ((string) $html !== '')
            {!! $html !!}
        @else
            <section class="vc-section">
                <div class="vc-container">
                    <h1 class="vc-heading">{{ $page?->title ?? 'Главная страница' }}</h1>
                </div>
            </section>
        @endif
    </main>
    @if (config_value('pwa.enabled', false))
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('{{ route('frontend.service-worker') }}').catch(() => {});
                });
            }
        </script>
    @endif
</body>
</html>
