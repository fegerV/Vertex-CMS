@php
    $type = $settings['type'] ?? 'info';
    $title = $settings['title'] ?? '';
    $content = $settings['content'] ?? '';
    $closable = $settings['closable'] ?? true;
@endphp

<div class="pb-alert pb-alert--{{ $type }}" role="alert">
    @if($closable)
        <button class="pb-alert__close" aria-label="Закрыть">×</button>
    @endif
    @if($title)
        <strong class="pb-alert__title">{{ $title }}</strong>
    @endif
    @if($content)
        <span class="pb-alert__content">{{ $content }}</span>
    @endif
</div>
