{{-- resources/views/builder/blocks/progress-bar.blade.php --}}
@php
    $value = $settings['value'] ?? 0;
    $max = $settings['max'] ?? 100;
    $color = $settings['color'] ?? '#3b82f6';
    $height = $settings['height'] ?? 8;
    $showLabel = $settings['show_label'] ?? true;
    $cssClass = $settings['css_class'] ?? '';
    
    $percentage = min(100, max(0, ($value / $max) * 100));
@endphp

<div class="vc-progress-bar {{ $cssClass }}">
    @if($showLabel)
        <div class="flex justify-between mb-2">
            <span class="text-sm font-medium">{{ number_format($value) }} / {{ number_format($max) }}</span>
            <span class="text-sm font-medium">{{ number_format($percentage, 1) }}%</span>
        </div>
    @endif
    <div class="w-full bg-slate-200 rounded-full overflow-hidden" style="height: {{ $height }}px;">
        <div class="h-full rounded-full transition-all duration-500" 
             style="width: {{ $percentage }}%; background-color: {{ $color }};"></div>
    </div>
</div>
