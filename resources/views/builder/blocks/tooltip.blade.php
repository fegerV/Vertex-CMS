@php
    $text = $settings['text'] ?? 'Наведите на меня';
    $tooltipContent = $settings['content'] ?? 'Это текст подсказки';
    $position = $settings['position'] ?? 'top';
@endphp

<span class="pb-tooltip-wrapper pb-tooltip--{{ $position }}">
    {{ $text }}
    <span class="pb-tooltip">
        {{ $tooltipContent }}
    </span>
</span>
