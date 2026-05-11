<!DOCTYPE html>
<html lang="{{ config_value('site.locale', 'ru') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $term->name }} | {{ config_value('site.name', 'VertexCMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-950">
    <main class="vc-page">
        <section class="vc-section">
            <div class="vc-container">
                <p class="vc-text">{{ $taxonomy->name }}</p>
                <h1 class="vc-heading">{{ $term->name }}</h1>
                @if ($term->description)
                    <div class="vc-text">{{ $term->description }}</div>
                @endif

                <div class="mt-8 space-y-6">
                    @forelse ($pages as $page)
                        <article class="rounded-2xl border border-slate-200 p-5">
                            <h2 class="text-xl font-semibold">
                                <a href="{{ url($page->uri) }}" class="hover:underline">{{ $page->title }}</a>
                            </h2>
                            @if ($page->seoMeta?->description)
                                <p class="mt-2 text-sm text-slate-600">{{ $page->seoMeta->description }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">No published pages are attached to this term yet.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </main>
</body>
</html>
