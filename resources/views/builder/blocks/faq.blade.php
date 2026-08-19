<div class="vc-faq space-y-6">
    @foreach($settings['items'] ?? [] as $item)
        <div class="vc-faq-item">
            <h3 class="text-lg font-bold mb-2">{{ $item['question'] ?? '' }}</h3>
            <div class="text-gray-600">
                {!! nl2br(e($item['answer'] ?? '')) !!}
            </div>
        </div>
    @endforeach
</div>
