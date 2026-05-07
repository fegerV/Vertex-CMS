@php
    $value = $settings['value'] ?? 0;
    $prefix = $settings['prefix'] ?? '';
    $suffix = $settings['suffix'] ?? '';
    $duration = $settings['duration'] ?? 2000;
    $label = $settings['label'] ?? '';
@endphp

<div class="vc-counter text-center p-4" x-data="{ 
    current: 0, 
    target: {{ $value }}, 
    duration: {{ $duration }},
    start() {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / this.duration, 1);
            this.current = Math.floor(progress * this.target);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
}" x-intersect.once="start()">
    <div class="text-4xl font-bold text-blue-600 mb-1">
        <span>{{ $prefix }}</span><span x-text="current">0</span><span>{{ $suffix }}</span>
    </div>
    @if($label)
        <div class="text-gray-600 font-medium">{{ $label }}</div>
    @endif
</div>
