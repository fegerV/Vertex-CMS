@php
    $title = $form->name ?: config_value('site.name', 'VertexCMS');
    $description = $form->description ?: config_value('seo.default_description', '');
    $themeColor = config_value('pwa.theme_color', '#020617');
@endphp
<!DOCTYPE html>
<html lang="{{ config_value('site.locale', 'ru') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    <meta name="theme-color" content="{{ $themeColor }}">
    @if (! app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-50 text-slate-950">
    <main class="min-h-screen px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl">
            <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_30px_80px_rgba(15,23,42,0.08)]">
                <div class="border-b border-slate-200 bg-gradient-to-br from-slate-950 via-slate-900 to-teal-950 px-6 py-8 text-white sm:px-8">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-teal-200">VertexCMS Forms</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight">{{ $title }}</h1>
                    @if ($description)
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200">{{ $description }}</p>
                    @endif
                </div>

                <div class="px-6 py-8 sm:px-8">
                    @include('forms::blocks.form', [
                        'form' => $form,
                        'formConfig' => $formConfig,
                        'actionUrl' => $actionUrl,
                        'settings' => $settings,
                    ])
                </div>
            </div>
        </div>
    </main>
</body>
</html>
