@php
    $text = $settings['text'] ?? 'Кнопка';
    $url = $settings['url'] ?? '#';
    $target = $settings['target'] ?? '_self';
    $style = $settings['style'] ?? 'primary';
    $size = $settings['size'] ?? 'md';
    $icon = $settings['icon'] ?? null;
@endphp

<p class="pb-button-wrap">
    <a 
        href="{{ $url }}"
        target="{{ $target }}"
        rel="{{ $target === '_blank' ? 'noopener noreferrer' : '' }}"
        class="pb-button pb-button--{{ $style }} pb-button--{{ $size }}"
    >
        @if($icon)
            <span class="pb-button__icon">{{ $icon }}</span>
        @endif
        <span class="pb-button__text">{{ $text }}</span>
    </a>
</p>
