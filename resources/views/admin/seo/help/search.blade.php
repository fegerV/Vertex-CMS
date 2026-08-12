@extends('admin.layouts.app')

@section('title', 'Результаты поиска по справке')

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.seo.help.index') }}" class="text-decoration-none">
                            <i class="bi bi-house me-1"></i>Справка
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Поиск
                    </li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-1 text-gray-800">
                        <i class="bi bi-search text-primary me-2"></i>
                        Результаты поиска
                    </h1>
                    @if($query)
                    <p class="text-muted mb-0">
                        По запросу: <strong>"{{ $query }}"</strong> найдено {{ count($results) }} результатов
                    </p>
                    @endif
                </div>
                <div>
                    <a href="{{ route('admin.seo.help.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Назад к справке
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Форма поиска -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="{{ route('admin.seo.help.search') }}" method="GET" class="position-relative">
                        <div class="input-group input-group-lg">
                            <input type="text"
                                   name="q"
                                   class="form-control"
                                   placeholder="Введите запрос для поиска по справке..."
                                   value="{{ $query }}"
                                   autofocus>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-search me-2"></i>
                                Найти
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Результаты поиска -->
    @if(count($results) > 0)
    <div class="row">
        <div class="col-12">
            <div class="list-group shadow-sm border-0">
                @foreach($results as $result)
                <a href="{{ route('admin.seo.help.show', ['section' => $result['section'], 'topic' => $result['topic'] ?? null]) }}"
                   class="list-group-item list-group-item-action p-4 border-bottom-0">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-2 text-primary">
                                <i class="bi bi-file-text me-2"></i>
                                {{ $result['title'] }}
                            </h5>
                            <p class="text-muted mb-2">{{ $result['excerpt'] }}</p>
                            <small class="text-secondary">
                                <i class="bi bi-folder me-1"></i>
                                Раздел: {{ $this->getSectionTitle($result['section']) }}
                            </small>
                        </div>
                        <div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Пагинация (заглушка для будущего функционала) -->
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Пагинация результатов">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#">Предыдущая</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item disabled">
                        <a class="page-link" href="#">Следующая</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
    @else
    <!-- Нет результатов -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-5 text-center">
                    <i class="bi bi-search display-1 text-muted mb-3 d-block"></i>
                    <h4 class="mb-2">Ничего не найдено</h4>
                    <p class="text-muted mb-4">
                        К сожалению, по вашему запросу не найдено ни одной статьи.
                    </p>

                    <div class="row g-3 justify-content-center">
                        <div class="col-md-8">
                            <div class="alert alert-info text-start">
                                <h6 class="alert-heading"><i class="bi bi-lightbulb me-2"></i>Советы по поиску:</h6>
                                <ul class="mb-0 small">
                                    <li>Проверьте правильность написания слов</li>
                                    <li>Используйте более общие термины</li>
                                    <li>Попробуйте синонимы вашего запроса</li>
                                    <li>Уменьшите количество слов в запросе</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.seo.help.index') }}" class="btn btn-primary me-2">
                            <i class="bi bi-book me-2"></i>
                            Все разделы справки
                        </a>
                        <a href="#" class="btn btn-outline-success">
                            <i class="bi bi-chat-dots me-2"></i>
                            Задать вопрос поддержке
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Популярные темы -->
    @if(count($results) === 0 || $query)
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-fire text-danger me-2"></i>
                        Популярные статьи справки
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'dashboard', 'topic' => 'seo-score']) }}"
                               class="text-decoration-none d-block p-3 rounded-3 bg-light hover-bg-primary transition-all">
                                <i class="bi bi-speedometer2 text-primary me-2"></i>
                                Понимание SEO Score
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'redirects', 'topic' => 'redirect-types']) }}"
                               class="text-decoration-none d-block p-3 rounded-3 bg-light hover-bg-primary transition-all">
                                <i class="bi bi-arrow-left-right text-primary me-2"></i>
                                Типы редиректов (301 vs 302)
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'social-media', 'topic' => 'what-is-og']) }}"
                               class="text-decoration-none d-block p-3 rounded-3 bg-light hover-bg-primary transition-all">
                                <i class="bi bi-share text-primary me-2"></i>
                                Что такое Open Graph
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'ai-assistant', 'topic' => 'ai-meta']) }}"
                               class="text-decoration-none d-block p-3 rounded-3 bg-light hover-bg-primary transition-all">
                                <i class="bi bi-robot text-primary me-2"></i>
                                AI генерация мета-тегов
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'redirects', 'topic' => '404-monitor']) }}"
                               class="text-decoration-none d-block p-3 rounded-3 bg-light hover-bg-primary transition-all">
                                <i class="bi bi-exclamation-triangle text-primary me-2"></i>
                                Мониторинг 404 ошибок
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'images', 'topic' => 'alt-optimization']) }}"
                               class="text-decoration-none d-block p-3 rounded-3 bg-light hover-bg-primary transition-all">
                                <i class="bi bi-image text-primary me-2"></i>
                                Оптимизация ALT-тегов
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.hover-bg-primary:hover {
    background-color: #e7f1ff !important;
}
.transition-all {
    transition: all 0.3s ease;
}
</style>

@php
function getSectionTitle($section) {
    $titles = [
        'dashboard' => 'Дашборд',
        'analysis' => 'Анализ контента',
        'redirects' => 'Редиректы',
        'social-media' => 'Социальные сети',
        'ai-assistant' => 'AI Ассистент',
        'schema' => 'Schema.org',
        'indexnow' => 'Мгновенная индексация',
        'keyword-maps' => 'Карты ключевых слов',
        'images' => 'Изображения',
        'tools' => 'Инструменты',
    ];
    return $titles[$section] ?? ucfirst($section);
}
@endphp
@endsection
