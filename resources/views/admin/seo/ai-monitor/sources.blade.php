@extends('admin.layouts.app')

@section('title', 'Источники цитирования (Citation Audit)')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Анализ источников цитирования</h1>
        <a href="{{ route('admin.seo.ai-monitor.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Назад к дашборду
        </a>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        Страницы вашего сайта, которые чаще всего используются ИИ-моделями в качестве источников информации.
        Чем выше Authority Score, тем более авторитетным считается контент.
    </div>

    <div class="row">
        @foreach($sources as $source)
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card shadow h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0">{{ Str::limit($source['page_title'], 40) }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">URL:</small>
                        <code>{{ $source['url'] }}</code>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Упоминаний:</span>
                        <strong>{{ $source['mentions_count'] }}</strong>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $source['authority_score'] }}%" 
                             aria-valuenow="{{ $source['authority_score'] }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Authority Score:</small>
                        <strong>{{ $source['authority_score'] }}/100</strong>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <a href="{{ $source['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary btn-block">
                        <i class="fas fa-eye"></i> Просмотреть страницу
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Рекомендации по улучшению цитируемости</h6>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <strong>Создавайте оригинальные исследования и кейсы</strong> — уникальный данные чаще цитируются ИИ.
                </li>
                <li class="list-group-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <strong>Добавляйте экспертные мнения</strong> — цитаты экспертов повышают доверие моделей.
                </li>
                <li class="list-group-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <strong>Используйте структурированные данные</strong> — таблицы, списки и FAQ легче парсятся.
                </li>
                <li class="list-group-item">
                    <i class="fas fa-check-circle text-success"></i>
                    <strong>Обновляйте контент регулярно</strong> — свежие данные имеют приоритет в ответах ИИ.
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
