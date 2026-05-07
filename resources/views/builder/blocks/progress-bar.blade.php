@php
    $value = $settings['value'] ?? 0;
    $max = $settings['max'] ?? 100;
    $color = $settings['color'] ?? '#3b82f6';
    $height = $settings['height'] ?? 8;
    $showLabel = $settings['show_label'] ?? true;
    
    $percentage = ($max > 0) ? ($value / $max) * 100 : 0;
    $percentage = min(100, max(0, $percentage));
@endphp

<div class="vc-progress-bar mb-4">
    <div class="flex justify-between items-center mb-1">
        @if($showLabel)
            <span class="text-sm font-medium text-gray-700">{{ round($percentage) }}%</span>
        @endif
    </div>
    <div class="w-full bg-gray-200 rounded-full" style="height: {{ $height }}px;">
        <div class="rounded-full transition-all duration-500 ease-out" 
             style="height: {{ $height }}px; width: {{ $percentage }}%; background-color: {{ $color }};">
        </div>
    </div>
</div>
