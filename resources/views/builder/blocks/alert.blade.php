@php
    $type = $settings['type'] ?? 'info';
    $title = $settings['title'] ?? '';
    $content = $settings['content'] ?? '';
    $closable = $settings['closable'] ?? true;
    
    $typeClasses = [
        'info' => 'bg-blue-50 text-blue-800 border-blue-200',
        'success' => 'bg-green-50 text-green-800 border-green-200',
        'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
        'error' => 'bg-red-50 text-red-800 border-red-200',
    ];
    
    $classString = $typeClasses[$type] ?? $typeClasses['info'];
@endphp

<div class="vc-alert p-4 border rounded {{ $classString }} mb-4" x-data="{ show: true }" x-show="show">
    <div class="flex justify-between items-start">
        <div>
            @if($title)
                <h4 class="font-bold mb-1">{{ $title }}</h4>
            @endif
            <div class="text-sm">
                {!! nl2br(e($content)) !!}
            </div>
        </div>
        @if($closable)
            <button @click="show = false" class="text-current opacity-50 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
</div>
