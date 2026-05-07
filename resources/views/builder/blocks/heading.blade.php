@php
    $level = $settings['level'] ?? 'h2';
    $align = $settings['align'] ?? 'left';
    $color = $settings['color'] ?? 'inherit';
    $fontSize = $settings['font_size'] ?? '';
@endphp

<{{ $level }} class="vc-heading" style="text-align: {{ $align }}; color: {{ $color }}; @if($fontSize) font-size: {{ $fontSize }}; @endif">
    {{ $settings['text'] ?? '' }}
</{{ $level }}>
