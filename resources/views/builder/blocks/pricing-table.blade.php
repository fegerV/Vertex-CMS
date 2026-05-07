@php
    $plans = $settings['plans'] ?? [];
    $columns = $settings['columns'] ?? 3;
    
    $gridCols = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
    ];
@endphp

<div class="vc-pricing grid {{ $gridCols[$columns] ?? 'grid-cols-3' }} gap-6">
    @foreach($plans as $plan)
        <div class="vc-plan border rounded-xl p-6 flex flex-col {{ ($plan['highlighted'] ?? false) ? 'border-blue-500 ring-2 ring-blue-500 ring-opacity-50 z-10 scale-105 bg-white shadow-lg' : 'border-gray-200' }}">
            @if($plan['highlighted'] ?? false)
                <span class="bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full self-start mb-4 uppercase tracking-wider">Most Popular</span>
            @endif
            
            <h3 class="text-xl font-bold mb-2">{{ $plan['name'] ?? '' }}</h3>
            
            <div class="flex items-baseline mb-6">
                <span class="text-4xl font-extrabold">{{ $plan['currency'] ?? '$' }}{{ $plan['price'] ?? 0 }}</span>
                <span class="text-gray-500 ml-1">/{{ $plan['period'] ?? 'mo' }}</span>
            </div>
            
            <ul class="space-y-3 mb-8 flex-grow">
                @foreach($plan['features'] ?? [] as $feature)
                    <li class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>
            
            <a href="#" class="block text-center py-3 px-6 rounded-lg font-bold transition-colors {{ ($plan['highlighted'] ?? false) ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                {{ $plan['button_text'] ?? 'Choose Plan' }}
            </a>
        </div>
    @endforeach
</div>
