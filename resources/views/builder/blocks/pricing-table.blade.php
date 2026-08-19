@php
    $plans = $settings['plans'] ?? [];
    $columns = $settings['columns'] ?? 3;
@endphp

<div class="pb-pricing pb-pricing--cols-{{ $columns }}">
    @foreach($plans as $plan)
        <div class="pb-pricing-plan {{ $plan['highlighted'] ?? false ? 'pb-pricing-plan--highlighted' : '' }}">
            @if($plan['highlighted'] ?? false)
                <span class="pb-pricing-plan__badge">Популярный</span>
            @endif
            <div class="pb-pricing-plan__header">
                <h3 class="pb-pricing-plan__name">{{ $plan['name'] ?? '' }}</h3>
                <div class="pb-pricing-plan__price">
                    <span class="pb-pricing-plan__currency">{{ $plan['currency'] ?? '$' }}</span>
                    <span class="pb-pricing-plan__amount">{{ $plan['price'] ?? 0 }}</span>
                    @if(isset($plan['period']))
                        <span class="pb-pricing-plan__period">/{{ $plan['period'] }}</span>
                    @endif
                </div>
            </div>
            @if(isset($plan['features']) && is_array($plan['features']))
                <ul class="pb-pricing-plan__features">
                    @foreach($plan['features'] as $feature)
                        <li class="pb-pricing-plan__feature">{{ $feature }}</li>
                    @endforeach
                </ul>
            @endif
            <div class="pb-pricing-plan__footer">
                <a href="#" class="pb-button pb-button--primary">{{ $plan['button_text'] ?? 'Выбрать' }}</a>
            </div>
        </div>
    @endforeach
</div>
