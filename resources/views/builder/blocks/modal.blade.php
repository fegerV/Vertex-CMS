@php
    $triggerText = $settings['trigger_text'] ?? 'Открыть модальное окно';
    $title = $settings['title'] ?? '';
    $content = $settings['content'] ?? '';
    $size = $settings['size'] ?? 'md';
@endphp

<div class="pb-modal-wrapper">
    <button class="pb-button pb-button--primary pb-modal__trigger" data-modal-open>
        {{ $triggerText }}
    </button>
    
    <div 
        class="pb-modal pb-modal--{{ $size }}" 
        data-modal 
        style="display: none;"
        aria-hidden="true"
    >
        <div class="pb-modal__overlay" data-modal-close></div>
        <div class="pb-modal__dialog">
            <div class="pb-modal__header">
                @if($title)
                    <h3 class="pb-modal__title">{{ $title }}</h3>
                @endif
                <button class="pb-modal__close" data-modal-close aria-label="Закрыть">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="pb-modal__body">
                {!! nl2br(e($content)) !!}
            </div>
        </div>
    </div>
</div>
