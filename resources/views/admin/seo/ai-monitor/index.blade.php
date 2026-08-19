@extends('admin.layouts.app')

@section('title', 'AI Мониторинг Бренда')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            AI Мониторинг Бренда: {{ $brandName }}
            @if($isDemoMode ?? false)
                <span class="badge badge-warning ml-2" title="Демо режим - данные симулированы">DEMO</span>
            @endif
        </h1>
        <a href="{{ route('admin.seo.ai-monitor.refresh') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-sync-alt"></i> Обновить данные
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($isDemoMode ?? false)
        <div class="alert alert-warning mb-4">
            <strong><i class="fas fa-exclamation-triangle"></i> Демо режим:</strong> 
            Этот экран показывает симулированные данные для демонстрации. 
            Для получения реальных данных настройте интеграцию с AI API и установите 
            <code>AI_FEATURE_BRAND_MONITOR=true</code> в конфигурации.
        </div>
    @endif

    <!-- Карточки статистики -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Всего упоминаний</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($data['stats']['total_mentions']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bullhorn fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Позитивный тон</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['stats']['positive_percent'] }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-smile fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Источники (Citations)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['stats']['sources_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-link fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Видимость (Тренд)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['stats']['visibility_trend'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- График видимости -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Сравнение видимости с конкурентами</h6>
                </div>
                <div class="card-body">
                    <canvas id="visibilityChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Тональность -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Распределение тональности</h6>
                </div>
                <div class="card-body">
                    <canvas id="sentimentChart" height="200"></canvas>
                    <div class="mt-3 text-center">
                        <span class="badge badge-success">Позитив: {{ $data['stats']['positive_percent'] }}%</span>
                        <span class="badge badge-secondary">Нейтрально: {{ 100 - $data['stats']['positive_percent'] - 15 }}%</span>
                        <span class="badge badge-danger">Негатив: 15%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Последние упоминания -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Последние упоминания в AI моделях</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Модель ИИ</th>
                            <th>Запрос</th>
                            <th>Тональность</th>
                            <th>Источник</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($data['mentions'], 0, 5) as $mention)
                        <tr>
                            <td>{{ $mention['date'] }}</td>
                            <td><span class="badge badge-info">{{ $mention['model'] }}</span></td>
                            <td>{{ Str::limit($mention['query'], 40) }}</td>
                            <td>
                                @if($mention['sentiment'] === 'positive')
                                    <span class="badge badge-success">Позитив</span>
                                @elseif($mention['sentiment'] === 'negative')
                                    <span class="badge badge-danger">Негатив</span>
                                @else
                                    <span class="badge badge-secondary">Нейтрально</span>
                                @endif
                            </td>
                            <td>
                                @if($mention['is_source'])
                                    <a href="{{ $mention['source_url'] }}" target="_blank" class="text-success"><i class="fas fa-check-circle"></i> Используется как источник</a>
                                @else
                                    <span class="text-muted">Просто упомянуто</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="showSnippet({{ $mention['id'] }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('admin.seo.ai-monitor.mentions') }}" class="btn btn-sm btn-outline-secondary">Показать все упоминания</a>
            </div>
        </div>
    </div>

    <!-- Возможности для роста -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning">
            <h6 class="m-0 font-weight-bold text-white">Возможности для увеличения трафика (AI Insights)</h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($data['opportunities'] as $opp)
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-{{ $opp['priority'] === 'high' ? 'danger' : 'info' }}">
                        <div class="card-body">
                            <h5 class="card-title">
                                @if($opp['priority'] === 'high')
                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                @else
                                    <i class="fas fa-lightbulb text-info"></i>
                                @endif
                                {{ $opp['title'] }}
                            </h5>
                            <p class="card-text small">{{ $opp['description'] }}</p>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge badge-success">Потенциал: {{ $opp['potential_traffic'] }}</span>
                                <small class="text-muted">{{ $opp['action'] }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// График видимости
const ctxVis = document.getElementById('visibilityChart').getContext('2d');
new Chart(ctxVis, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($data['competitor_analysis'], 'name')) !!},
        datasets: [{
            label: 'Score видимости',
            data: {!! json_encode(array_column($data['competitor_analysis'], 'visibility_score')) !!},
            backgroundColor: [
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 99, 132, 0.5)',
                'rgba(255, 206, 86, 0.5)',
                'rgba(75, 192, 192, 0.5)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, max: 100 }
        }
    }
});

// График тональности
const ctxSent = document.getElementById('sentimentChart').getContext('2d');
new Chart(ctxSent, {
    type: 'doughnut',
    data: {
        labels: ['Позитив', 'Нейтрально', 'Негатив'],
        datasets: [{
            data: [{{ $data['stats']['positive_percent'] }}, {{ 100 - $data['stats']['positive_percent'] - 15 }}, 15],
            backgroundColor: ['#28a745', '#6c757d', '#dc3545']
        }]
    }
});

function showSnippet(id) {
    // Здесь можно открыть модальное окно с полным текстом упоминания
    alert('Просмотр полного сниппета для ID: ' + id);
}
</script>
@endsection
