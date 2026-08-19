@php
    $count = $settings['count'] ?? 6;
    $category = $settings['category'] ?? null;
    $showImage = $settings['show_image'] ?? true;
    $showExcerpt = $settings['show_excerpt'] ?? true;
    $showDate = $settings['show_date'] ?? true;
    $columns = $settings['columns'] ?? 3;
    $layout = $settings['layout'] ?? 'grid';
    
    // Получаем новости из базы (пример)
    $news = \App\Models\Post::when($category, fn($q) => $q->where('category', $category))
        ->latest()
        ->limit($count)
        ->get();
@endphp

<div class="pb-news-feed pb-news-feed--cols-{{ $columns }} pb-news-feed--{{ $layout }}">
    @forelse($news as $item)
        <article class="pb-news-item">
            @if($showImage && $item->featured_image)
                <div class="pb-news-item__image">
                    <img src="{{ asset('storage/' . $item->featured_image) }}" alt="{{ $item->title }}" loading="lazy"/>
                </div>
            @endif
            <div class="pb-news-item__content">
                @if($showDate && $item->published_at)
                    <span class="pb-news-item__date">{{ $item->published_at->format('d.m.Y') }}</span>
                @endif
                <h3 class="pb-news-item__title">{{ $item->title }}</h3>
                @if($showExcerpt && $item->excerpt)
                    <p class="pb-news-item__excerpt">{{ Str::limit($item->excerpt, 120) }}</p>
                @endif
                <a href="{{ route('posts.show', $item) }}" class="pb-news-item__link">Подробнее →</a>
            </div>
        </article>
    @empty
        <p class="pb-news-feed__empty">Новости не найдены</p>
    @endforelse
</div>
