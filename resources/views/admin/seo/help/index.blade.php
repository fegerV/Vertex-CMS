@extends('admin.layouts.app')

@section('title', 'Справка Vertex SEO Pro')

@section('content')
<div class="container-fluid py-4">
    <!-- Заголовок и поиск -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h1 class="h3 mb-1 text-gray-800">
                                <i class="bi bi-book-half text-primary me-2"></i>
                                Справка Vertex SEO Pro
                            </h1>
                            <p class="text-muted mb-0">Полное руководство по использованию всех инструментов SEO-модуля</p>
                        </div>
                        <div style="max-width: 400px; width: 100%;">
                            <form action="{{ route('admin.seo.help.search') }}" method="GET" class="position-relative">
                                <input type="text" 
                                       name="q" 
                                       class="form-control form-control-lg" 
                                       placeholder="Поиск по справке..."
                                       value="{{ request('q') }}">
                                <button type="submit" class="btn btn-primary position-absolute end-0 top-0 h-100 px-3">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Секции справки -->
    <div class="row g-4">
        @foreach($sections as $key => $section)
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 shadow-sm border-0 hover-shadow transition-all">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-square bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="bi {{ $section['icon'] }} fs-4"></i>
                        </div>
                        <h5 class="mb-0">{{ $section['title'] }}</h5>
                    </div>
                </div>
                <div class="card-body px-4">
                    <p class="text-muted mb-3">{{ $section['description'] }}</p>
                    
                    <hr class="my-3">
                    
                    <h6 class="small text-uppercase text-muted fw-bold mb-3">Темы раздела:</h6>
                    <ul class="list-unstyled mb-0">
                        @foreach($section['topics'] as $topic)
                        <li class="mb-2">
                            <a href="{{ route('admin.seo.help.show', ['section' => $key, 'topic' => $topic['anchor']]) }}" 
                               class="text-decoration-none text-dark hover-primary d-flex align-items-start">
                                <i class="bi bi-chevron-right small text-primary me-2 mt-1"></i>
                                <span>{{ $topic['name'] }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-footer bg-white border-0 px-4 pb-4">
                    <a href="{{ route('admin.seo.help.show', ['section' => $key]) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-book me-2"></i>
                        Открыть раздел
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Быстрые ссылки -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <h5 class="mb-3">
                        <i class="bi bi-lightning-charge me-2"></i>
                        Популярные статьи
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'dashboard', 'topic' => 'seo-score']) }}" 
                               class="text-white text-decoration-none d-block p-3 rounded-3 bg-white bg-opacity-10 hover-bg-opacity-20 transition-all">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Понимание SEO Score
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'redirects', 'topic' => 'redirect-types']) }}" 
                               class="text-white text-decoration-none d-block p-3 rounded-3 bg-white bg-opacity-10 hover-bg-opacity-20 transition-all">
                                <i class="bi bi-arrow-left-right me-2"></i>
                                Типы редиректов
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'social-media', 'topic' => 'what-is-og']) }}" 
                               class="text-white text-decoration-none d-block p-3 rounded-3 bg-white bg-opacity-10 hover-bg-opacity-20 transition-all">
                                <i class="bi bi-share me-2"></i>
                                Что такое Open Graph
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.seo.help.show', ['section' => 'ai-assistant', 'topic' => 'ai-meta']) }}" 
                               class="text-white text-decoration-none d-block p-3 rounded-3 bg-white bg-opacity-10 hover-bg-opacity-20 transition-all">
                                <i class="bi bi-robot me-2"></i>
                                AI генерация мета-тегов
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Поддержка -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Не нашли ответ на свой вопрос?</strong>
                    <p class="mb-0">Обратитесь в нашу службу поддержки — мы поможем решить любую проблему.</p>
                </div>
                <a href="#" class="btn btn-outline-info ms-auto">
                    <i class="bi bi-headset me-2"></i>
                    Связаться с поддержкой
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
    transform: translateY(-2px);
}
.icon-square {
    min-width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hover-primary:hover {
    color: #0d6efd !important;
}
.hover-bg-opacity-20:hover {
    background-color: rgba(255, 255, 255, 0.2) !important;
}
.transition-all {
    transition: all 0.3s ease;
}
</style>
@endsection
