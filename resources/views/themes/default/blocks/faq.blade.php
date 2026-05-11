<div class="vc-faq">
    @foreach (($settings['items'] ?? []) as $item)
        <details class="vc-faq-item">
            <summary>{{ $item['question'] ?? '' }}</summary>
            <div>{!! nl2br(e($item['answer'] ?? '')) !!}</div>
        </details>
    @endforeach
</div>
