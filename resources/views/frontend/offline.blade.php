<!DOCTYPE html>
<html lang="{{ config_value('site.locale', 'ru') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page?->title ?? 'Offline' }}</title>
    @if (config_value('pwa.enabled', false))
        <meta name="theme-color" content="{{ config_value('pwa.theme_color', '#020617') }}">
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
                    <h1 class="vc-heading">You are offline</h1>
                    <div class="vc-text">The requested page is not available right now. Reconnect and try again.</div>
                </div>
            </section>
        @endif
    </main>
</body>
</html>
