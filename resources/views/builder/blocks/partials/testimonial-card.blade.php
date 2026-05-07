<div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 flex flex-col h-full">
    @if($showRating)
        <div class="flex text-yellow-400 mb-4">
            @for($i = 0; $i < 5; $i++)
                <svg class="w-5 h-5 {{ $i < ($item['rating'] ?? 5) ? 'fill-current' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            @endfor
        </div>
    @endif
    
    <blockquote class="text-gray-700 italic flex-grow mb-6">
        "{{ $item['text'] ?? '' }}"
    </blockquote>
    
    <div class="flex items-center">
        @if(!empty($item['avatar']))
            <img src="{{ $item['avatar'] }}" alt="{{ $item['author'] ?? '' }}" class="w-12 h-12 rounded-full mr-4 object-cover border-2 border-white shadow-sm">
        @else
            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold mr-4">
                {{ substr($item['author'] ?? 'U', 0, 1) }}
            </div>
        @endif
        <div>
            <div class="font-bold text-gray-900">{{ $item['author'] ?? 'Anonymous' }}</div>
            <div class="text-xs text-gray-500 uppercase tracking-wider">{{ $item['role'] ?? '' }}</div>
        </div>
    </div>
</div>
