@php
    $align = $settings['align'] ?? 'left';
    $color = $settings['color'] ?? 'inherit';
    $fontSize = $settings['font_size'] ?? '';
    $lineHeight = $settings['line_height'] ?? '1.6';
@endphp

<div class="vc-text" style="text-align: {{ $align }}; color: {{ $color }}; @if($fontSize) font-size: {{ $fontSize }}; @endif line-height: {{ $lineHeight }};">
    {!! nl2br(e($settings['content'] ?? '')) !!}
</div>
