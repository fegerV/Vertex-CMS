@php
    $maxWidth = $settings['max_width'] ?? '7xl';
    $paddingTop = $settings['padding_top'] ?? 16;
    $paddingBottom = $settings['padding_bottom'] ?? 16;
    $paddingLeft = $settings['padding_left'] ?? 4;
    $paddingRight = $settings['padding_right'] ?? 4;
    
    $maxWidthClasses = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
    ];
@endphp

<div class="vc-container mx-auto {{ $maxWidthClasses[$maxWidth] ?? 'max-w-7xl' }}" 
     style="padding-top: {{ $paddingTop }}px; padding-bottom: {{ $paddingBottom }}px; padding-left: {{ $paddingLeft }}px; padding-right: {{ $paddingRight }}px;">
    @foreach($settings['blocks'] ?? [] as $block)
        {!! app(\App\Builder\Services\PageBuilderService::class)->compileBlock($block['type'] ?? 'unknown', $block['settings'] ?? []) !!}
    @endforeach
</div>
