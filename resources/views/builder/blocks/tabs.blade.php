@php
    $tabs = $settings['tabs'] ?? [];
    $style = $settings['style'] ?? 'line';
    $alignment = $settings['alignment'] ?? 'left';
    
    $alignClasses = [
        'left' => 'justify-start',
        'center' => 'justify-center',
        'right' => 'justify-end',
    ];
    
    $styles = [
        'line' => [
            'nav' => 'border-b border-gray-200',
            'tab' => 'py-2 px-4 border-b-2 transition-colors',
            'active' => 'border-blue-600 text-blue-600',
            'inactive' => 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
        ],
        'boxed' => [
            'nav' => 'flex-wrap border border-gray-200 rounded-t-lg bg-gray-50',
            'tab' => 'py-2 px-4 transition-colors',
            'active' => 'bg-white text-blue-600 font-medium',
            'inactive' => 'text-gray-500 hover:text-gray-700',
        ],
        'pill' => [
            'nav' => 'space-x-2',
            'tab' => 'py-2 px-4 rounded-full transition-colors',
            'active' => 'bg-blue-600 text-white',
            'inactive' => 'bg-gray-100 text-gray-500 hover:bg-gray-200',
        ],
    ];
    
    $currentStyle = $styles[$style] ?? $styles['line'];
@endphp

<div class="vc-tabs" x-data="{ activeTab: 0 }">
    <div class="vc-tabs-nav flex {{ $alignClasses[$alignment] ?? 'justify-start' }} {{ $currentStyle['nav'] }}">
        @foreach($tabs as $index => $tab)
            <button 
                @click="activeTab = {{ $index }}"
                class="{{ $currentStyle['tab'] }}"
                :class="activeTab === {{ $index }} ? '{{ $currentStyle['active'] }}' : '{{ $currentStyle['inactive'] }}'"
            >
                {{ $tab['title'] ?? 'Tab ' . ($index + 1) }}
            </button>
        @endforeach
    </div>
    <div class="vc-tabs-content mt-4">
        @foreach($tabs as $index => $tab)
            <div x-show="activeTab === {{ $index }}" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                {!! $tab['content'] ?? '' !!}
            </div>
        @endforeach
    </div>
</div>
