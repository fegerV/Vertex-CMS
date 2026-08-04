@php
    $items = $settings['items'] ?? [];
    $allowMultiple = $settings['allow_multiple'] ?? false;
@endphp

<div class="pb-accordion" @if(!$allowMultiple) data-allow-multiple="false" @endif>
    @foreach($items as $index => $item)
        <div class="pb-accordion-item {{ ($item['open'] ?? false) ? 'is-active' : '' }}">
            <button 
                class="pb-accordion-item__header"
                aria-expanded="{{ ($item['open'] ?? false) ? 'true' : 'false' }}"
            >
                <span class="pb-accordion-item__title">{{ $item['title'] ?? '' }}</span>
                <span class="pb-accordion-item__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </span>
            </button>
            <div class="pb-accordion-item__content" style="display: {{ ($item['open'] ?? false) ? 'block' : 'none' }}">
                <div class="pb-accordion-item__body">
                    {!! nl2br(e($item['content'] ?? '')) !!}
                </div>
            </div>
        </div>
    @endforeach
</div>
