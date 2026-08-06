@extends('admin.layouts.app')

@section('title', 'Создать карту ключевых слов')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plus-circle me-2"></i>Создание новой карты ключевых слов
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.seo.keyword-maps.store') }}" method="POST">
                        @csrf
                        
                        <!-- Название -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Название <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required
                                   placeholder="Например: SEO Guide Link">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Описательное название для этой карты (например, "SEO Guide Link")</small>
                        </div>

                        <!-- Целевой URL -->
                        <div class="mb-3">
                            <label for="target_url" class="form-label">Целевой URL <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                                <input type="url" class="form-control @error('target_url') is-invalid @enderror" 
                                       id="target_url" name="target_url" value="{{ old('target_url') }}" required
                                       placeholder="https://example.com/page">
                                @error('target_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">URL, на который будут вести ссылки при совпадении ключевых фраз</small>
                        </div>

                        <!-- Ключевые слова -->
                        <div class="mb-3">
                            <label class="form-label">Ключевые слова <span class="text-danger">*</span></label>
                            <div id="keywordsContainer">
                                <div class="input-group mb-2 keyword-item">
                                    <input type="text" class="form-control" name="keywords[]" placeholder="Ключевая фраза" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="removeKeyword(this)" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addKeyword()">
                                <i class="fas fa-plus me-1"></i>Добавить ключевое слово
                            </button>
                            @error('keywords')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Добавьте все варианты ключевых фраз, которые должны вести на целевой URL</small>
                        </div>

                        <!-- AI Варианты (будут сгенерированы после создания) -->
                        <div class="alert alert-info">
                            <i class="fas fa-magic me-2"></i>
                            <strong>AI-помощник:</strong> После создания карты вы сможете сгенерировать дополнительные варианты ключевых слов с помощью ИИ.
                        </div>

                        <!-- Описание -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Опциональное описание того, что делает это правило">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Настройки правила -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="m-0"><i class="fas fa-cog me-2"></i>Настройки правила</h6>
                            </div>
                            <div class="card-body">
                                <!-- Активность -->
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" checked>
                                    <label class="form-check-label" for="is_enabled">
                                        <strong>Включено</strong>
                                    </label>
                                    <small class="text-muted d-block">Активируйте правило для применения к контенту</small>
                                </div>

                                <!-- Чувствительность к регистру -->
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="case_sensitive" name="case_sensitive">
                                    <label class="form-check-label" for="case_sensitive">
                                        <strong>Чувствительно к регистру</strong>
                                    </label>
                                    <small class="text-muted d-block">Только точное совпадение регистра ключевых фраз</small>
                                </div>

                                <!-- Максимум ссылок на пост -->
                                <div class="mb-3">
                                    <label for="max_links_per_post" class="form-label">Макс. ссылок на запись</label>
                                    <input type="number" class="form-control @error('max_links_per_post') is-invalid @enderror" 
                                           id="max_links_per_post" name="max_links_per_post" value="3" min="1" max="50">
                                    @error('max_links_per_post')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Ограничивает количество раз, когда эта ссылка появляется в одной записи (1-50)</small>
                                </div>

                                <!-- Авто-линки при публикации -->
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="auto_link_on_publish" name="auto_link_on_publish" checked>
                                    <label class="form-check-label" for="auto_link_on_publish">
                                        <strong>Авто-линки при публикации</strong>
                                    </label>
                                    <small class="text-muted d-block">Автоматически применять ссылки к новому контенту; ссылки появляются в течение 1-2 минут после публикации</small>
                                </div>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.seo.keyword-maps.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Создать карту
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Боковая панель с подсказками -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-lightbulb me-2"></i>Советы по использованию</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Выбирайте релевантные ключевые слова:</strong> Добавляйте только те фразы, которые действительно относятся к содержанию целевой страницы.
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Не переусердствуйте:</strong> Установите разумный лимит ссылок (2-5), чтобы не создавать спам.
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Используйте AI-варианты:</strong> После создания нажмите кнопку генерации AI-вариантов для расширения списка ключевых фраз.
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Проверяйте предпросмотр:</strong> Используйте функцию предпросмотра, чтобы увидеть, как ссылки будут добавлены в контент.
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <strong>Регулярно обновляйте:</strong> Периодически проверяйте и обновляйте карты ключевых слов для поддержания актуальности.
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0"><i class="fas fa-exclamation-triangle me-2"></i>Важно</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0 small">
                        Избегайте создания слишком большого количества авто-ссылок на одну страницу. Это может быть воспринято поисковыми системами как спам. 
                        Используйте карты ключевых слов умеренно и только для действительно полезной внутренней перелинковки.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addKeyword() {
    const container = document.getElementById('keywordsContainer');
    const div = document.createElement('div');
    div.className = 'input-group mb-2 keyword-item';
    div.innerHTML = `
        <input type="text" class="form-control" name="keywords[]" placeholder="Ключевая фраза" required>
        <button type="button" class="btn btn-outline-danger" onclick="removeKeyword(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeKeyword(button) {
    const items = document.querySelectorAll('.keyword-item');
    if (items.length > 1) {
        button.closest('.keyword-item').remove();
    } else {
        // Очищаем поле вместо удаления последнего
        button.closest('.keyword-item').querySelector('input').value = '';
    }
}
</script>
@endpush
@endsection
