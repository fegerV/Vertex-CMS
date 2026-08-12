@php
    $level = $settings['level'] ?? 'h2';
    $text = $settings['text'] ?? '';
    $align = $settings['align'] ?? 'left';
    $color = $settings['color'] ?? null;
    $fontSize = $settings['font_size'] ?? null;
    $fontWeight = $settings['font_weight'] ?? null;
@endphp

<{{ $level }} class="vc-heading"
    style="
        text-align: {{ $align }};
        @if($color) color: {{ $color }}; @endif
        @if($fontSize) font-size: {{ $fontSize }}; @endif
        @if($fontWeight) font-weight: {{ $fontWeight }}; @endif
    "
>{{ $text }}</{{ $level }}>
