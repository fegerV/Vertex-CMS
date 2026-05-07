@php
    $text = $settings['text'] ?? '';
    $content = $settings['content'] ?? '';
    $position = $settings['position'] ?? 'top';
    
    $positions = [
        'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
    ];
    
    $arrows = [
        'top' => 'top-full left-1/2 -translate-x-1/2 border-t-gray-800',
        'bottom' => 'bottom-full left-1/2 -translate-x-1/2 border-b-gray-800',
        'left' => 'left-full top-1/2 -translate-y-1/2 border-l-gray-800',
        'right' => 'right-full top-1/2 -translate-y-1/2 border-r-gray-800',
    ];
@endphp

<span class="vc-tooltip relative inline-block group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
    <span class="underline decoration-dotted cursor-help">{{ $text }}</span>
    
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="absolute z-50 px-3 py-2 text-xs font-medium text-white bg-gray-800 rounded-lg shadow-lg whitespace-nowrap {{ $positions[$position] ?? $positions['top'] }}"
        style="display: none;"
    >
        {{ $content }}
        <div class="absolute w-0 h-0 border-4 border-transparent {{ $arrows[$position] ?? $arrows['top'] }}"></div>
    </div>
</span>
