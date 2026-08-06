@php
    $style = $settings['style'] ?? 'solid';
    $color = $settings['color'] ?? '#e5e7eb';
    $thickness = $settings['thickness'] ?? 1;
    $width = $settings['width'] ?? '100%';
@endphp

<hr 
    class="pb-divider pb-divider--{{ $style }}"
    style="
        border-top: {{ $thickness }}px {{ $style }} {{ $color }};
        width: {{ $width }};
    "
/>
