@php
    $value = $settings['value'] ?? 100;
    $suffix = $settings['suffix'] ?? '+';
    $prefix = $settings['prefix'] ?? '';
    $duration = $settings['duration'] ?? 2000;
    $label = $settings['label'] ?? '';
@endphp

<div class="pb-counter" data-value="{{ $value }}" data-duration="{{ $duration }}">
    <div class="pb-counter__number">
        <span class="pb-counter__prefix">{{ $prefix }}</span>
        <span class="pb-counter__value">0</span>
        <span class="pb-counter__suffix">{{ $suffix }}</span>
    </div>
    @if($label)
        <div class="pb-counter__label">{{ $label }}</div>
    @endif
</div>
