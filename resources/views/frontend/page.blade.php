@php
    $builderPreview = (bool) ($builderPreview ?? false);
    $seo = $page?->seoMeta;
    $title = $seo?->title ?: ($page?->title ?? 'VertexCMS');
    $description = $seo?->description ?: config_value('seo.default_description', '');
    $canonical = $seo?->canonical_url ?: url($page?->uri ?? '/');
    $robots = $seo?->robots ?: 'index, follow';
    $ogTitle = $seo?->og_title ?: $title;
    $ogDescription = $seo?->og_description ?: $description;
    $gdprSettings = \App\Models\GdprSetting::getActive();
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
        @if ($builderPreview)
            @vite(['resources/css/app.css'])
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/telegram-widget.js'])
        @endif
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

    @if($gdprSettings->enabled && !$gdprSettings->wasRecentlyCreated)
        <div id="gdpr-cookie-banner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 z-50 hidden">
            <div class="container mx-auto max-w-6xl">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-sm">
                        <p class="font-semibold mb-1">{{ $gdprSettings->banner_title }}</p>
                        <p>{{ $gdprSettings->banner_message }}</p>
                        @if($gdprSettings->policy_link)
                            <a href="{{ $gdprSettings->policy_link }}" target="_blank" class="underline hover:text-gray-300">
                                Политика конфиденциальности
                            </a>
                        @endif
                    </div>
                    <div class="flex gap-2 flex-shrink-0">
                        <button id="gdpr-decline" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm">
                            {{ $gdprSettings->decline_button_text }}
                        </button>
                        <button id="gdpr-accept" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded text-sm font-semibold">
                            {{ $gdprSettings->accept_button_text }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const banner = document.getElementById('gdpr-cookie-banner');
                const acceptBtn = document.getElementById('gdpr-accept');
                const declineBtn = document.getElementById('gdpr-decline');

                if (!document.cookie.includes('gdpr_accepted=')) {
                    banner.classList.remove('hidden');
                }

                acceptBtn.addEventListener('click', function() {
                    const expires = new Date();
                    expires.setDate(expires.getDate() + {{ $gdprSettings->cookie_duration_days }});
                    document.cookie = 'gdpr_accepted=true;expires=' + expires.toUTCString() + ';path=/';
                    banner.classList.add('hidden');
                });

                declineBtn.addEventListener('click', function() {
                    const expires = new Date();
                    expires.setDate(expires.getDate() + 1);
                    document.cookie = 'gdpr_accepted=false;expires=' + expires.toUTCString() + ';path=/';
                    banner.classList.add('hidden');
                });
            });
        </script>
    @endif

    <!-- Telegram Widget -->
    @if (! $builderPreview && config_value('telegram.enabled', false))
        <script>
           window.telegramWidgetConfig = {!! json_encode([
               'enabled' => true,
               'username' => config_value('telegram.username', ''),
               'widget_style' => config_value('telegram.widget_style', 'floating'),
               'widget_position' => config_value('telegram.widget_position', 'bottom-right'),
               'greeting' => config_value('telegram.greeting', null),
               'color' => config_value('telegram.color', '#0088cc'),
               'show_online_status' => config_value('telegram.show_online_status', false),
               'message_prefill' => config_value('telegram.message_prefill', null),
               'bot_token' => config_value('telegram.bot_token', null),
               'chat_id' => config_value('telegram.chat_id', null),
           ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
        </script>
        <div id="telegram-widget"></div>
    @endif

    @if (! $builderPreview && config_value('pwa.enabled', false))
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