@php
    $tabs = $settings['tabs'] ?? [];
    $style = $settings['style'] ?? 'line';
    $alignment = $settings['alignment'] ?? 'left';
@endphp

<div class="pb-tabs pb-tabs--{{ $style }} pb-tabs--{{ $alignment }}">
    <div class="pb-tabs__nav">
        @foreach($tabs as $index => $tab)
            <button 
                class="pb-tabs__tab {{ $index === 0 ? 'is-active' : '' }}"
                data-tab-index="{{ $index }}"
                aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
            >
                {{ $tab['title'] ?? '' }}
            </button>
        @endforeach
    </div>
    <div class="pb-tabs__content">
        @foreach($tabs as $index => $tab)
            <div 
                class="pb-tabs__panel {{ $index === 0 ? 'is-active' : '' }}"
                data-tab-panel="{{ $index }}"
            >
                {!! nl2br(e($tab['content'] ?? '')) !!}
            </div>
        @endforeach
    </div>
</div>
