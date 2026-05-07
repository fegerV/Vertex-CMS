@php
    $columns = $settings['columns'] ?? 3;
    $gap = $settings['gap'] ?? 'md';
    $radius = $settings['radius'] ?? 'md';
    
    $gaps = [
        'sm' => 'gap-2',
        'md' => 'gap-4',
        'lg' => 'gap-8',
    ];
    
    $radiusClasses = [
        'none' => '',
        'sm' => 'rounded-sm',
        'md' => 'rounded-md',
        'lg' => 'rounded-lg',
    ];
    
    $gridCols = [
        1 => 'grid-cols-1',
        2 => 'grid-cols-2',
        3 => 'grid-cols-2 md:grid-cols-3',
        4 => 'grid-cols-2 md:grid-cols-4',
        5 => 'grid-cols-2 md:grid-cols-5',
        6 => 'grid-cols-3 md:grid-cols-6',
    ];
@endphp

<div class="vc-gallery grid {{ $gridCols[$columns] ?? 'grid-cols-3' }} {{ $gaps[$gap] ?? 'gap-4' }}">
    @foreach($settings['images'] ?? [] as $image)
        <div class="vc-gallery-item">
            @php
                // If media_id is provided, we should ideally fetch the URL from Media service
                // For now, if url is available in the repeater item (might need to adjust based on actual data structure)
                $imageUrl = $image['url'] ?? ''; 
                if (empty($imageUrl) && !empty($image['media_id'])) {
                    // Fallback or placeholder if only media_id exists
                    $imageUrl = '/api/media/' . $image['media_id'];
                }
            @endphp
            <img src="{{ $imageUrl }}" alt="{{ $image['alt'] ?? '' }}" class="w-full h-full object-cover {{ $radiusClasses[$radius] ?? '' }}">
        </div>
    @endforeach
</div>
