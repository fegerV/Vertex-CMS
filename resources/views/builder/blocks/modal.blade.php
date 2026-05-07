@php
    $triggerText = $settings['trigger_text'] ?? 'Open Modal';
    $title = $settings['title'] ?? '';
    $content = $settings['content'] ?? '';
    $size = $settings['size'] ?? 'md';
    
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
    ];
@endphp

<div class="vc-modal-container" x-data="{ open: false }">
    <button @click="open = true" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
        {{ $triggerText }}
    </button>

    <div 
        x-show="open" 
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="display: none;"
    >
        <div class="flex items-center justify-center min-h-screen p-4">
            {{-- Backdrop --}}
            <div 
                x-show="open" 
                @click="open = false"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            ></div>

            {{-- Modal Content --}}
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                class="relative bg-white rounded-xl shadow-2xl w-full {{ $sizes[$size] ?? 'max-w-md' }} overflow-hidden"
            >
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">{{ $title }}</h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="px-6 py-4">
                    {!! $content !!}
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end">
                    <button @click="open = false" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
