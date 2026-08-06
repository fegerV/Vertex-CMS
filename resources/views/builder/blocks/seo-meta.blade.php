{{-- resources/views/builder/blocks/seo-meta.blade.php --}}
@php
    $title = $settings['title'] ?? '';
    $description = $settings['description'] ?? '';
    $keywords = is_array($settings['keywords']) ? implode(', ', $settings['keywords']) : ($settings['keywords'] ?? '');
    $robots = $settings['robots'] ?? 'index, follow';
    $canonical = $settings['canonical'] ?? '';
@endphp

@if($title || $description || $keywords)
<div class="vc-seo-meta hidden">
    @if($title)<title>{{ $title }}</title>@endif
    @if($description)<meta name="description" content="{{ $description }}">@endif
    @if($keywords)<meta name="keywords" content="{{ $keywords }}">@endif
    <meta name="robots" content="{{ $robots }}">
    @if($canonical)<link rel="canonical" href="{{ $canonical }}">@endif
</div>
@endif
