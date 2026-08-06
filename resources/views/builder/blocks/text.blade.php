@php
    $content = $settings['content'] ?? '';
    $align = $settings['align'] ?? 'left';
    $color = $settings['color'] ?? null;
    $fontSize = $settings['font_size'] ?? null;
    $lineHeight = $settings['line_height'] ?? '1.6';
@endphp

<div 
    class="pb-text"
    style="
        text-align: {{ $align }};
        @if($color) color: {{ $color }}; @endif
        @if($fontSize) font-size: {{ $fontSize }}; @endif
        line-height: {{ $lineHeight }};
    "
>
    {!! nl2br(e($content)) !!}
</div>
