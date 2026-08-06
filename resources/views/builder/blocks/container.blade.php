{{-- resources/views/builder/blocks/container.blade.php --}}
@php
    $maxWidth = $settings['max_width'] ?? '7xl';
    $padding = $settings['padding'] ?? ['top' => 16, 'bottom' => 16, 'left' => 4, 'right' => 4];
    $blocks = $settings['blocks'] ?? [];
    $cssClass = $settings['css_class'] ?? '';
    $bgColor = $settings['background_color'] ?? '';
    
    $maxWidthClasses = [
        'sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl', '3xl' => 'max-w-3xl', '4xl' => 'max-w-4xl', '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl', '7xl' => 'max-w-7xl',
    ];
@endphp

<div class="vc-container mx-auto {{ $maxWidthClasses[$maxWidth] ?? 'max-w-7xl' }} {{ $cssClass }}" 
     style="{{ $bgColor ? 'background-color: ' . $bgColor . ';' : '' }}; padding-top: {{ $padding['top'] ?? 0 }}px; padding-bottom: {{ $padding['bottom'] ?? 0 }}px; padding-left: {{ $padding['left'] ?? 0 }}px; padding-right: {{ $padding['right'] ?? 0 }}px;">
    @foreach($blocks as $block)
        @includeIf('builder.blocks.' . $block['_type'], ['settings' => $block['settings'] ?? []])
    @endforeach
</div>
