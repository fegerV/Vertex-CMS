@php
    $allowMultiple = $settings['allow_multiple'] ?? false;
@endphp

<div class="vc-accordion space-y-2" @if(!$allowMultiple) x-data="{ active: null }" @else x-data="{ active: [] }" @endif>
    @foreach($settings['items'] ?? [] as $index => $item)
        <div class="vc-accordion-item border rounded border-gray-200 overflow-hidden">
            <button 
                class="w-full px-4 py-3 text-left font-medium bg-gray-50 hover:bg-gray-100 flex justify-between items-center transition-colors"
                @if(!$allowMultiple)
                    @click="active = (active === {{ $index }} ? null : {{ $index }})"
                @else
                    @click="active.includes({{ $index }}) ? active = active.filter(i => i !== {{ $index }}) : active.push({{ $index }})"
                @endif
            >
                <span>{{ $item['title'] ?? 'Accordion Item' }}</span>
                <svg 
                    class="w-5 h-5 transition-transform duration-200" 
                    :class="(@if(!$allowMultiple) active === {{ $index }} @else active.includes({{ $index }}) @endif) ? 'rotate-180' : ''" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div 
                class="px-4 py-3 bg-white" 
                x-show="@if(!$allowMultiple) active === {{ $index }} @else active.includes({{ $index }}) @endif"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                style="display: none;"
            >
                {!! nl2br(e($item['content'] ?? '')) !!}
            </div>
        </div>
    @endforeach
</div>
