@extends('admin.layouts.app')

@section('title', $content['title'])

@section('content')
<div class="container-fluid py-4">
    <!-- Хлебные крошки и навигация -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.seo.help.index') }}" class="text-decoration-none">
                            <i class="bi bi-house me-1"></i>Справка
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.seo.help.show', ['section' => $section]) }}" class="text-decoration-none">
                            {{ $this->getSectionTitle($section) }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $content['title'] }}
                    </li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h3 mb-1 text-gray-800">
                        <i class="bi bi-file-text text-primary me-2"></i>
                        {{ $content['title'] }}
                    </h1>
                    <p class="text-muted mb-0">
                        <small>
                            <i class="bi bi-clock me-1"></i>
                            Обновлено: {{ $content['updated_at'] }}
                        </small>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.seo.help.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Назад к справке
                    </a>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>
                        Печать
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Основной контент статьи -->
    <div class="row">
        <!-- Боковая панель с оглавлением -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px; z-index: 1;">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-list-ul me-2"></i>
                        Содержание
                    </h6>
                </div>
                <div class="card-body" id="toc">
                    <nav id="TableOfContents">
                        <!-- Будет заполнено JavaScript -->
                    </nav>
                </div>
            </div>
        </div>

        <!-- Текст статьи -->
        <div class="col-lg-9">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="seo-help-content" id="articleContent">
                        {!! $this->parseMarkdown($content['content']) !!}
                    </div>
                </div>
            </div>

            <!-- Навигация между статьями -->
            <div class="row mt-4">
                <div class="col-6">
                    <a href="#" class="btn btn-outline-secondary w-100 text-start">
                        <i class="bi bi-chevron-left me-2"></i>
                        <small class="d-block text-muted">Предыдущая статья</small>
                        <span id="prevArticle">Загрузка...</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="#" class="btn btn-outline-secondary w-100 text-end">
                        <small class="d-block text-muted">Следующая статья</small>
                        <span id="nextArticle">Загрузка...</span>
                        <i class="bi bi-chevron-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Быстрая помощь -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-4">
                    <h5 class="mb-3">
                        <i class="bi bi-question-circle text-primary me-2"></i>
                        Нужна дополнительная помощь?
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-chat-dots text-success fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Чат поддержки</h6>
                                    <p class="text-muted small mb-0">Онлайн-консультант ответит на ваши вопросы</p>
                                    <a href="#" class="btn btn-sm btn-success mt-2">Начать чат</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-envelope text-primary fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Email поддержка</h6>
                                    <p class="text-muted small mb-0">Отправьте запрос и получите ответ в течение 24 часов</p>
                                    <a href="mailto:support@vertexseo.pro" class="btn btn-sm btn-primary mt-2">Написать</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-youtube text-danger fs-4"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-1">Видеоуроки</h6>
                                    <p class="text-muted small mb-0">Смотрите пошаговые руководства на YouTube</p>
                                    <a href="#" class="btn btn-sm btn-danger mt-2">Смотреть</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.seo-help-content {
    line-height: 1.8;
    font-size: 1.05rem;
}

.seo-help-content h1 {
    font-size: 2rem;
    margin-bottom: 1.5rem;
    color: #1a1a1a;
}

.seo-help-content h2 {
    font-size: 1.5rem;
    margin-top: 2.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
    color: #2c3e50;
}

.seo-help-content h3 {
    font-size: 1.25rem;
    margin-top: 2rem;
    margin-bottom: 0.75rem;
    color: #34495e;
}

.seo-help-content p {
    margin-bottom: 1.25rem;
    color: #495057;
}

.seo-help-content ul, 
.seo-help-content ol {
    margin-bottom: 1.25rem;
    padding-left: 1.5rem;
}

.seo-help-content li {
    margin-bottom: 0.5rem;
}

.seo-help-content code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.9em;
    color: #e83e8c;
}

.seo-help-content pre {
    background-color: #2d3748;
    color: #f7fafc;
    padding: 1.25rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin-bottom: 1.25rem;
}

.seo-help-content pre code {
    background: none;
    padding: 0;
    color: inherit;
}

.seo-help-content table {
    width: 100%;
    margin-bottom: 1.25rem;
    border-collapse: collapse;
}

.seo-help-content th,
.seo-help-content td {
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    text-align: left;
}

.seo-help-content th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.seo-help-content tr:nth-child(even) {
    background-color: #f8f9fa;
}

.seo-help-content blockquote {
    border-left: 4px solid #0d6efd;
    padding: 1rem 1.25rem;
    margin: 1.25rem 0;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
}

.seo-help-content blockquote p {
    margin-bottom: 0;
    color: #495057;
}

.seo-help-content .alert {
    margin-bottom: 1.25rem;
}

#TableOfContents ul {
    list-style: none;
    padding-left: 0;
}

#TableOfContents li {
    margin-bottom: 0.5rem;
}

#TableOfContents a {
    color: #495057;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.2s;
}

#TableOfContents a:hover {
    color: #0d6efd;
}

#TableOfContents ul ul {
    padding-left: 1rem;
    margin-top: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Генерация оглавления
    const content = document.getElementById('articleContent');
    const toc = document.getElementById('TableOfContents');
    const headings = content.querySelectorAll('h2, h3');
    
    if (headings.length > 0) {
        let tocHTML = '<ul>';
        
        headings.forEach((heading, index) => {
            const id = 'heading-' + index;
            heading.id = id;
            
            if (heading.tagName === 'H2') {
                tocHTML += `<li><a href="#${id}">${heading.textContent}</a></li>`;
            } else {
                tocHTML += `<li><ul><li><a href="#${id}">${heading.textContent}</a></li></ul></li>`;
            }
        });
        
        tocHTML += '</ul>';
        toc.innerHTML = tocHTML;
    }
    
    // Плавная прокрутка к якорям
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>

@php
// Вспомогательные методы для отображения
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

function parseMarkdown($text) {
    // Простой парсер Markdown
    $text = preg_replace('/^# (.*+)$/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/^## (.*+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^### (.*+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/`(.+?)`/', '<code>$1</code>', $text);
    $text = preg_replace('/^- (.*+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/^> (.*+)$/m', '<blockquote><p>$1</p></blockquote>', $text);
    $text = preg_replace('/```(\w*)\n(.*?)```/s', '<pre><code>$2</code></pre>', $text);
    $text = nl2br($text);
    return $text;
}
@endphp
@endsection
