@extends('admin.layouts.app')

@section('title', 'Сравнение с конкурентами')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Анализ видимости vs Конкуренты</h1>
        <a href="{{ route('admin.seo.ai-monitor.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Назад к дашборду
        </a>
    </div>

    <div class="row mb-4">
        @foreach($comparison as $comp)
        <div class="col-md-3">
            <div class="card {{ $comp['name'] === config('app.name') ? 'border-primary border-2' : '' }} shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">{{ $comp['name'] }}</h5>
                    <div class="my-3">
                        <canvas id="radar{{ $loop->index }}" width="150" height="150"></canvas>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Видимость:</small>
                        <h3 class="mb-0">{{ $comp['visibility_score'] }}</h3>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Упоминаний:</small>
                        <h5 class="mb-0">{{ $comp['mention_volume'] }}</h5>
                    </div>
                    <div>
                        <small class="text-muted">Тональность:</small>
                        <span class="badge {{ $comp['sentiment_ratio'] >= 0.7 ? 'badge-success' : ($comp['sentiment_ratio'] >= 0.5 ? 'badge-warning' : 'badge-danger') }}">
                            {{ round($comp['sentiment_ratio'] * 100) }}% позитива
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Детальное сравнение метрик</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Бренд</th>
                            <th>Score видимости</th>
                            <th>Объем упоминаний</th>
                            <th>Позитивная тональность</th>
                            <th>Доля рынка (AI)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalMentions = array_sum(array_column($comparison, 'mention_volume')); @endphp
                        @foreach($comparison as $comp)
                        <tr class="{{ $comp['name'] === config('app.name') ? 'table-active' : '' }}">
                            <td>
                                <strong>{{ $comp['name'] }}</strong>
                                @if($comp['name'] === config('app.name'))
                                    <span class="badge badge-primary">Ваш бренд</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar {{ $comp['visibility_score'] >= 80 ? 'bg-success' : ($comp['visibility_score'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                         style="width: {{ $comp['visibility_score'] }}%">
                                        {{ $comp['visibility_score'] }}
                                    </div>
                                </div>
                            </td>
                            <td>{{ number_format($comp['mention_volume']) }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 mr-2" style="height: 10px;">
                                        <div class="progress-bar bg-success" style="width: {{ $comp['sentiment_ratio'] * 100 }}%"></div>
                                    </div>
                                    <small>{{ round($comp['sentiment_ratio'] * 100) }}%</small>
                                </div>
                            </td>
                            <td>
                                @php $marketShare = round(($comp['mention_volume'] / $totalMentions) * 100, 1); @endphp
                                <strong>{{ $marketShare }}%</strong>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="alert alert-warning mt-4">
        <h6><i class="fas fa-lightbulb"></i> Стратегические рекомендации:</h6>
        <ul class="mb-0">
            @foreach($comparison as $comp)
                @if($comp['name'] !== config('app.name') && $comp['visibility_score'] > 80)
                    <li>
                        <strong>{{ $comp['name'] }}</strong> лидирует по видимости. Изучите их контент-стратегию и источники обратных ссылок.
                    </li>
                @endif
            @endforeach
            <li>Фокусируйтесь на создании контента, который решает конкретные проблемы пользователей — это повышает шанс цитирования в AI Overviews.</li>
            <li>Увеличивайте присутствие в экспертных сообществах и отраслевых изданиях для роста Authority Score.</li>
        </ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@foreach($comparison as $index => $comp)
const ctxRadar{{ $index }} = document.getElementById('radar{{ $index }}').getContext('2d');
new Chart(ctxRadar{{ $index }}, {
    type: 'doughnut',
    data: {
        labels: ['Видимость', 'Остальное'],
        datasets: [{
            data: [{{ $comp['visibility_score'] }}, {{ 100 - $comp['visibility_score'] }}],
            backgroundColor: ['#4e73df', '#eaecf4'],
            borderWidth: 0
        }]
    },
    options: {
        cutout: '70%',
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
        }
    }
});
@endforeach
</script>
@endsection
