@extends('admin.layouts.app')

@section('title', 'Использование ИИ - Vertex SEO Pro')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-robot text-primary"></i> Использование ИИ
                </h1>
                <div>
                    <button class="btn btn-outline-secondary me-2" onclick="exportReport('csv')">
                        <i class="fas fa-file-csv"></i> Экспорт CSV
                    </button>
                    <button class="btn btn-outline-secondary me-2" onclick="exportReport('pdf')">
                        <i class="fas fa-file-pdf"></i> Экспорт PDF
                    </button>
                    <form action="{{ route('admin.seo.ai.sync') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i> Обновить
                        </button>
                    </form>
                </div>
            </div>
            <p class="text-muted mt-2">
                Отслеживайте использование AI-функций в этом месяце ({{ $currentMonth }})
                @if($lastSync)
                    <span class="badge bg-info ms-2">Последняя синхронизация: {{ $lastSync->diffForHumans() }}</span>
                @endif
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Сводная статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Всего запросов
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format(array_sum($usageStats)) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-comments fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Лимит месяца
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format(array_sum($limits)) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Осталось
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $totalUsed = array_sum($usageStats);
                                    $totalLimit = array_sum($limits);
                                    $remaining = max(0, $totalLimit - $totalUsed);
                                @endphp
                                {{ number_format($remaining) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Использовано
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $percentage = $totalLimit > 0 ? round(($totalUsed / $totalLimit) * 100, 1) : 0;
                                @endphp
                                {{ $percentage }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percent fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Прогресс бар общего использования -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Общий прогресс использования</h6>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 30px;">
                        @php
                            $colors = ['bg-success', 'bg-warning', 'bg-danger'];
                            $colorIndex = $percentage < 50 ? 0 : ($percentage < 80 ? 1 : 2);
                        @endphp
                        <div class="progress-bar {{ $colors[$colorIndex] }}" role="progressbar" 
                             style="width: {{ $percentage }}%;" 
                             aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $percentage }}%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted">
                        <small>Начало месяца</small>
                        <small>Конец месяца</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Детальная статистика по категориям -->
    <div class="row">
        <!-- Исследование и анализ -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-search"></i> Исследование и анализ
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Функция</th>
                                    <th>Использовано</th>
                                    <th>Лимит</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $researchFeatures = ['keywords_research', 'topic_research'];
                                @endphp
                                @foreach($researchFeatures as $feature)
                                    @if(isset($usageStats[$feature]))
                                        @php
                                            $used = $usageStats[$feature];
                                            $limit = $limits[$feature] ?? 1000;
                                            $percent = min(100, round(($used / $limit) * 100, 1));
                                            $featureName = ucwords(str_replace('_', ' ', $feature));
                                        @endphp
                                        <tr>
                                            <td>{{ $featureName }}</td>
                                            <td><strong>{{ number_format($used) }}</strong></td>
                                            <td>{{ number_format($limit) }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                                         role="progressbar" style="width: {{ $percent }}%;">
                                                        {{ $percent }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Изображения -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-image"></i> Изображения
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Функция</th>
                                    <th>Использовано</th>
                                    <th>Лимит</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $imageFeatures = ['image_alt'];
                                @endphp
                                @foreach($imageFeatures as $feature)
                                    @if(isset($usageStats[$feature]))
                                        @php
                                            $used = $usageStats[$feature];
                                            $limit = $limits[$feature] ?? 500;
                                            $percent = min(100, round(($used / $limit) * 100, 1));
                                            $featureName = 'Alt для изображений';
                                        @endphp
                                        <tr>
                                            <td>{{ $featureName }}</td>
                                            <td><strong>{{ number_format($used) }}</strong></td>
                                            <td>{{ number_format($limit) }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                                         role="progressbar" style="width: {{ $percent }}%;">
                                                        {{ $percent }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Написание контента -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-pen-fancy"></i> Написание контента
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Функция</th>
                                    <th>Использовано</th>
                                    <th>Лимит</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $contentFeatures = ['content_write', 'write_more', 'article_master', 'paragraph', 
                                                       'paragraph_rewrite', 'sentence_expander', 'summarizer', 'analogy', 'free_form'];
                                @endphp
                                @foreach($contentFeatures as $feature)
                                    @if(isset($usageStats[$feature]))
                                        @php
                                            $used = $usageStats[$feature];
                                            $limit = $limits[$feature] ?? 1000;
                                            $percent = min(100, round(($used / $limit) * 100, 1));
                                            $featureName = ucwords(str_replace('_', ' ', $feature));
                                        @endphp
                                        <tr>
                                            <td>{{ $featureName }}</td>
                                            <td><strong>{{ number_format($used) }}</strong></td>
                                            <td>{{ number_format($limit) }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                                         role="progressbar" style="width: {{ $percent }}%;">
                                                        {{ $percent }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Чат и помощь -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-comments"></i> Чат и помощь
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Функция</th>
                                    <th>Использовано</th>
                                    <th>Лимит</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $chatFeatures = ['chat', 'fix_ai', 'grammar_fix', 'ai_team'];
                                @endphp
                                @foreach($chatFeatures as $feature)
                                    @if(isset($usageStats[$feature]))
                                        @php
                                            $used = $usageStats[$feature];
                                            $limit = $limits[$feature] ?? 1000;
                                            $percent = min(100, round(($used / $limit) * 100, 1));
                                            $featureName = $feature === 'chat' ? 'Чат (RankBot)' : ucwords(str_replace('_', ' ', $feature));
                                        @endphp
                                        <tr>
                                            <td>{{ $featureName }}</td>
                                            <td><strong>{{ number_format($used) }}</strong></td>
                                            <td>{{ number_format($limit) }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                                         role="progressbar" style="width: {{ $percent }}%;">
                                                        {{ $percent }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO мета -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-meta"></i> SEO мета
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Функция</th>
                                    <th>Использовано</th>
                                    <th>Лимит</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $seoFeatures = ['bulk_meta', 'seo_title', 'seo_description', 'seo_meta', 'open_graph'];
                                @endphp
                                @foreach($seoFeatures as $feature)
                                    @if(isset($usageStats[$feature]))
                                        @php
                                            $used = $usageStats[$feature];
                                            $limit = $limits[$feature] ?? 500;
                                            $percent = min(100, round(($used / $limit) * 100, 1));
                                            $featureName = $feature === 'bulk_meta' ? 'Пакетная SEO-мета' : 
                                                          ($feature === 'open_graph' ? 'Open Graph' : ucwords(str_replace('_', ' ', $feature)));
                                        @endphp
                                        <tr>
                                            <td>{{ $featureName }}</td>
                                            <td><strong>{{ number_format($used) }}</strong></td>
                                            <td>{{ number_format($limit) }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                                         role="progressbar" style="width: {{ $percent }}%;">
                                                        {{ $percent }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Блоги и статьи -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-purple text-white" style="background-color: #6f42c1 !important;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-newspaper"></i> Блоги и статьи
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Функция</th>
                                    <th>Использовано</th>
                                    <th>Лимит</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $blogFeatures = ['blog_idea', 'blog_plan', 'blog_intro', 'blog_conclusion', 'post_title'];
                                @endphp
                                @foreach($blogFeatures as $feature)
                                    @if(isset($usageStats[$feature]))
                                        @php
                                            $used = $usageStats[$feature];
                                            $limit = $limits[$feature] ?? 200;
                                            $percent = min(100, round(($used / $limit) * 100, 1));
                                            $featureMap = [
                                                'blog_idea' => 'Идея записи блога',
                                                'blog_plan' => 'План записи в блоге',
                                                'blog_intro' => 'Введение записи блога',
                                                'blog_conclusion' => 'Заключение записи блога',
                                                'post_title' => 'Заголовок записи'
                                            ];
                                            $featureName = $featureMap[$feature] ?? ucwords(str_replace('_', ' ', $feature));
                                        @endphp
                                        <tr>
                                            <td>{{ $featureName }}</td>
                                            <td><strong>{{ number_format($used) }}</strong></td>
                                            <td>{{ number_format($limit) }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                                         role="progressbar" style="width: {{ $percent }}%;">
                                                        {{ $percent }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Дополнительные категории (товары, соцсети, email и т.д.) -->
    <div class="row">
        <!-- Товары -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3" style="background-color: #e83e8c; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-box"></i> Товары
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @php
                            $productFeatures = ['product_description', 'product_pros_cons', 'product_review'];
                            $productNames = [
                                'product_description' => 'Описание товара',
                                'product_pros_cons' => 'Достоинства и недостатки',
                                'product_review' => 'Отзыв о товаре'
                            ];
                        @endphp
                        @foreach($productFeatures as $feature)
                            @if(isset($usageStats[$feature]))
                                @php
                                    $used = $usageStats[$feature];
                                    $limit = $limits[$feature] ?? 300;
                                    $percent = min(100, round(($used / $limit) * 100, 1));
                                @endphp
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $productNames[$feature] }}</h6>
                                        <small class="text-muted">{{ number_format($used) }}/{{ number_format($limit) }}</small>
                                    </div>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                             role="progressbar" style="width: {{ $percent }}%;">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Соцсети -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3" style="background-color: #1da1f2; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-share-alt"></i> Соцсети
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @php
                            $socialFeatures = ['facebook_post', 'facebook_comment_reply', 'tweet', 'tweet_reply', 'instagram_caption'];
                            $socialNames = [
                                'facebook_post' => 'Пост в Facebook',
                                'facebook_comment_reply' => 'Ответ на комментарий FB',
                                'tweet' => 'Твит',
                                'tweet_reply' => 'Ответ на твит',
                                'instagram_caption' => 'Подпись в Instagram'
                            ];
                        @endphp
                        @foreach($socialFeatures as $feature)
                            @if(isset($usageStats[$feature]))
                                @php
                                    $used = $usageStats[$feature];
                                    $limit = $limits[$feature] ?? 300;
                                    $percent = min(100, round(($used / $limit) * 100, 1));
                                @endphp
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $socialNames[$feature] }}</h6>
                                        <small class="text-muted">{{ number_format($used) }}/{{ number_format($limit) }}</small>
                                    </div>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                             role="progressbar" style="width: {{ $percent }}%;">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3" style="background-color: #dc3545; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-envelope"></i> Email
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @php
                            $emailFeatures = ['email', 'email_reply'];
                            $emailNames = [
                                'email' => 'Email',
                                'email_reply' => 'Ответ на письма'
                            ];
                        @endphp
                        @foreach($emailFeatures as $feature)
                            @if(isset($usageStats[$feature]))
                                @php
                                    $used = $usageStats[$feature];
                                    $limit = $limits[$feature] ?? 200;
                                    $percent = min(100, round(($used / $limit) * 100, 1));
                                @endphp
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">{{ $emailNames[$feature] }}</h6>
                                        <small class="text-muted">{{ number_format($used) }}/{{ number_format($limit) }}</small>
                                    </div>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                             role="progressbar" style="width: {{ $percent }}%;">
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Видео, подкасты и специальные функции -->
    <div class="row">
        <!-- Видео и подкасты -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3" style="background-color: #ff0000; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-video"></i> Видео и подкасты
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Функция</th>
                                    <th>Использовано</th>
                                    <th>Лимит</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $videoFeatures = ['youtube_script', 'youtube_description', 'podcast_plan', 'recipe'];
                                    $videoNames = [
                                        'youtube_script' => 'Сценарий видео на YouTube',
                                        'youtube_description' => 'Описание видео на YouTube',
                                        'podcast_plan' => 'План эпизода подкаста',
                                        'recipe' => 'Рецепт'
                                    ];
                                @endphp
                                @foreach($videoFeatures as $feature)
                                    @if(isset($usageStats[$feature]))
                                        @php
                                            $used = $usageStats[$feature];
                                            $limit = $limits[$feature] ?? 100;
                                            $percent = min(100, round(($used / $limit) * 100, 1));
                                        @endphp
                                        <tr>
                                            <td>{{ $videoNames[$feature] }}</td>
                                            <td><strong>{{ number_format($used) }}</strong></td>
                                            <td>{{ number_format($limit) }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                                         role="progressbar" style="width: {{ $percent }}%;">
                                                        {{ $percent }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ссылки -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3" style="background-color: #28a745; color: white;">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-link"></i> Ссылки
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Функция</th>
                                    <th>Использовано</th>
                                    <th>Лимит</th>
                                    <th>Прогресс</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $linkFeatures = ['link_opportunities', 'related_posts', 'link_suggestions'];
                                    $linkNames = [
                                        'link_opportunities' => 'Link Opportunities',
                                        'related_posts' => 'Related Posts',
                                        'link_suggestions' => 'Link Suggestions'
                                    ];
                                @endphp
                                @foreach($linkFeatures as $feature)
                                    @if(isset($usageStats[$feature]))
                                        @php
                                            $used = $usageStats[$feature];
                                            $limit = $limits[$feature] ?? 300;
                                            $percent = min(100, round(($used / $limit) * 100, 1));
                                        @endphp
                                        <tr>
                                            <td>{{ $linkNames[$feature] }}</td>
                                            <td><strong>{{ number_format($used) }}</strong></td>
                                            <td>{{ number_format($limit) }}</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $percent >= 80 ? 'bg-danger' : ($percent >= 50 ? 'bg-warning' : 'bg-success') }}" 
                                                         role="progressbar" style="width: {{ $percent }}%;">
                                                        {{ $percent }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport(format) {
    const month = '{{ $currentMonth }}';
    window.location.href = `/admin/seo/ai/export?format=${format}&month=${month}`;
}
</script>

<style>
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.border-left-info { border-left: 4px solid #36b9cc !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }
.bg-purple { background-color: #6f42c1 !important; }
</style>
@endsection
