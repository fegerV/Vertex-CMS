@extends('admin.layouts.app')

@section('title', 'Возможности роста (AI Opportunities)')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Возможности для увеличения трафика</h1>
        <a href="{{ route('admin.seo.ai-monitor.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Назад к дашборду
        </a>
    </div>

    <div class="alert alert-success">
        <i class="fas fa-robot"></i> 
        Эти рекомендации сгенерированы на основе анализа того, как ИИ-модели отвечают на запросы в вашей нише.
        Реализация этих пунктов может значительно увеличить видимость бренда в AI Overviews.
    </div>

    <div class="row">
        @foreach($opportunities as $index => $opp)
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100 border-left-{{ $opp['priority'] === 'high' ? 'danger' : 'info' }}">
                <div class="card-header d-flex justify-content-between align-items-center bg-white">
                    <h6 class="m-0 font-weight-bold">
                        @if($opp['type'] === 'content_gap')
                            <i class="fas fa-file-alt text-warning"></i> Content Gap
                        @elseif($opp['type'] === 'schema_missing')
                            <i class="fas fa-code text-info"></i> Микроразметка
                        @elseif($opp['type'] === 'authority_building')
                            <i class="fas fa-trophy text-primary"></i> Авторитет
                        @else
                            <i class="fas fa-star text-secondary"></i> Другое
                        @endif
                        {{ $opp['title'] }}
                    </h6>
                    <span class="badge badge-{{ $opp['priority'] === 'high' ? 'danger' : 'info' }}">
                        {{ $opp['priority'] === 'high' ? 'Высокий приоритет' : 'Средний приоритет' }}
                    </span>
                </div>
                <div class="card-body">
                    <p class="card-text">{{ $opp['description'] }}</p>
                    
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6><i class="fas fa-check-circle text-success"></i> Рекомендуемое действие:</h6>
                        <p class="mb-0 font-weight-bold">{{ $opp['action'] }}</p>
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Потенциал трафика:</small>
                            <h4 class="mb-0 text-success">{{ $opp['potential_traffic'] }}</h4>
                        </div>
                        <button class="btn btn-sm btn-primary" onclick="createTask({{ $index }})">
                            <i class="fas fa-plus-circle"></i> Создать задачу
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Секция с быстрыми действиями -->
    <div class="card shadow mt-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Быстрые действия для улучшения AI-видимости</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <button class="btn btn-outline-success btn-block h-100" onclick="alert('Запущен анализ контента...')">
                        <i class="fas fa-search"></i><br>
                        Аудит контента
                    </button>
                </div>
                <div class="col-md-3 mb-3">
                    <button class="btn btn-outline-info btn-block h-100" onclick="alert('Генерация Schema.org...')">
                        <i class="fas fa-code"></i><br>
                        Добавить разметку
                    </button>
                </div>
                <div class="col-md-3 mb-3">
                    <button class="btn btn-outline-warning btn-block h-100" onclick="alert('Поиск тем для статей...')">
                        <i class="fas fa-pen-fancy"></i><br>
                        Найти темы
                    </button>
                </div>
                <div class="col-md-3 mb-3">
                    <button class="btn btn-outline-danger btn-block h-100" onclick="alert('Анализ конкурентов...')">
                        <i class="fas fa-chart-line"></i><br>
                        Анализ лидеров
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Образовательный блок -->
    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Как работает AI SEO?</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-brain fa-3x text-primary mb-2"></i>
                        <h6>1. Анализ запросов</h6>
                        <p class="small text-muted">ИИ анализирует миллионы запросов и определяет, какой контент наиболее релевантен.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-link fa-3x text-success mb-2"></i>
                        <h6>2. Выбор источников</h6>
                        <p class="small text-muted">Модели выбирают источники с высоким Authority Score и уникальными данными.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center mb-3">
                        <i class="fas fa-bullhorn fa-3x text-warning mb-2"></i>
                        <h6>3. Генерация ответов</h6>
                        <p class="small text-muted">ИИ создает ответы, цитируя ваш контент, если он отвечает на вопрос пользователя.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createTask(index) {
    // Здесь можно интегрировать с системой задач или тикетов
    const opp = @json($opportunities);
    alert('Создание задачи: ' + opp[index].action + '\n\nВ полной версии это откроет форму создания задачи в проектном менеджере.');
}
</script>
@endsection
