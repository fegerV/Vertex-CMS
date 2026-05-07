@php
    $count = $settings['count'] ?? 6;
    $category = $settings['category'] ?? null;
    $showImage = $settings['show_image'] ?? true;
    $showExcerpt = $settings['show_excerpt'] ?? true;
    $showDate = $settings['show_date'] ?? true;
    $columns = $settings['columns'] ?? 3;
    $layout = $settings['layout'] ?? 'grid';
    
    // In a real application, we would fetch posts from a service
    // For now, we'll use empty placeholder if nothing passed
    $posts = $settings['posts'] ?? [];
    
    $gridCols = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-1 md:grid-cols-2',
        3 => 'grid-cols-1 md:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
    ];
@endphp

<div class="vc-news-feed">
    @if(empty($posts))
        <div class="text-center py-12 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
            <p class="text-gray-500">No posts found in {{ $category ?? 'all categories' }}</p>
        </div>
    @else
        <div class="grid {{ $gridCols[$columns] ?? 'grid-cols-3' }} gap-8">
            @foreach($posts as $post)
                <article class="flex flex-col h-full bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow border border-gray-100">
                    @if($showImage && !empty($post['image']))
                        <a href="{{ $post['url'] ?? '#' }}" class="block aspect-video overflow-hidden">
                            <img src="{{ $post['image'] }}" alt="{{ $post['title'] ?? '' }}" class="w-full h-full object-cover">
                        </a>
                    @endif
                    
                    <div class="p-5 flex flex-col flex-grow">
                        @if($showDate && !empty($post['date']))
                            <time class="text-xs text-gray-400 mb-2 uppercase tracking-widest font-semibold">{{ $post['date'] }}</time>
                        @endif
                        
                        <h3 class="text-xl font-bold mb-3 text-gray-900 hover:text-blue-600 transition-colors">
                            <a href="{{ $post['url'] ?? '#' }}">{{ $post['title'] ?? '' }}</a>
                        </h3>
                        
                        @if($showExcerpt && !empty($post['excerpt']))
                            <p class="text-gray-600 text-sm line-clamp-3 mb-4">{{ $post['excerpt'] }}</p>
                        @endif
                        
                        <a href="{{ $post['url'] ?? '#' }}" class="mt-auto text-blue-600 text-sm font-bold inline-flex items-center hover:translate-x-1 transition-transform">
                            Read More
                            <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
