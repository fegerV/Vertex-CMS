@php
    $title = $settings['title'] ?? 'Title';
    $content = $settings['content'] ?? '';
    $open = $settings['open'] ?? false;
@endphp

<div class="vc-collapse border border-gray-200 rounded-lg mb-4" x-data="{ isOpen: {{ $open ? 'true' : 'false' }} }">
    <button 
        @click="isOpen = !isOpen" 
        class="w-full flex items-center justify-between p-4 focus:outline-none"
    >
        <span class="font-medium text-gray-900">{{ $title }}</span>
        <svg 
            class="w-5 h-5 transition-transform duration-200" 
            :class="isOpen ? 'rotate-180' : ''" 
            fill="none" viewBox="0 0 24 24" stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div 
        x-show="isOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 max-h-0"
        x-transition:enter-end="opacity-100 max-h-screen"
        class="p-4 border-t border-gray-200 bg-gray-50 overflow-hidden"
    >
        {!! $content !!}
    </div>
</div>
