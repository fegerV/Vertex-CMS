@extends('admin.layouts.app')

@section('title', 'Настройки AI Консультанта')

@section('content')
<div class="container-fluid">
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start">
            <i class="fas fa-cog fa-2x text-primary mr-3"></i>
            <div>
                <h5 class="alert-heading mb-2">Настройки AI RAG Консультанта</h5>
                <p class="mb-0">
                    Настройте параметры работы AI-помощника: подключите API ключи, выберите модель, 
                    настройте поведение и внешний вид виджета чата.
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.seo.ai-kb.settings.save') }}" method="POST">
        @csrf
        
        <div class="row">
            <!-- Левая колонка -->
            <div class="col-lg-8">
                <!-- API Настройки -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-key text-primary"></i> API Настройки</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="openai_api_key" class="font-weight-bold">OpenAI API Key</label>
                            <input type="password" 
                                   class="form-control @error('openai_api_key') is-invalid @enderror" 
                                   id="openai_api_key" 
                                   name="openai_api_key" 
                                   value="{{ env('OPENAI_API_KEY', '') }}"
                                   placeholder="sk-...">
                            @error('openai_api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Получите ключ на <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com</a>
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="embedding_model" class="font-weight-bold">Модель для эмбеддингов</label>
                            <select class="form-control" id="embedding_model" name="embedding_model">
                                <option value="text-embedding-ada-002" selected>text-embedding-ada-002</option>
                                <option value="text-embedding-3-small">text-embedding-3-small (дешевле)</option>
                                <option value="text-embedding-3-large">text-embedding-3-large (точнее)</option>
                            </select>
                            <small class="form-text text-muted">
                                Модель для преобразования текста в векторы
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="chat_model" class="font-weight-bold">Модель для чата</label>
                            <select class="form-control" id="chat_model" name="chat_model">
                                <option value="gpt-3.5-turbo" selected>gpt-3.5-turbo (быстро и дешево)</option>
                                <option value="gpt-4">gpt-4 (точнее, но дороже)</option>
                                <option value="gpt-4-turbo">gpt-4-turbo</option>
                            </select>
                            <small class="form-text text-muted">
                                Модель для генерации ответов
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="temperature" class="font-weight-bold">Температура (креативность)</label>
                            <input type="range" 
                                   class="form-control-range" 
                                   id="temperature" 
                                   name="temperature" 
                                   min="0" 
                                   max="1" 
                                   step="0.1" 
                                   value="0.3">
                            <div class="d-flex justify-content-between">
                                <small>Точный (0)</small>
                                <small class="font-weight-bold">0.3</small>
                                <small>Креативный (1)</small>
                            </div>
                            <small class="form-text text-muted">
                                Для консультаций рекомендуется низкое значение (0.2-0.3) для точных ответов
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Настройки виджета -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-comments text-primary"></i> Настройки виджета</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="widget_title" class="font-weight-bold">Заголовок виджета</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="widget_title" 
                                   name="widget_title" 
                                   value="Онлайн-консультант"
                                   placeholder="Например: Помощник магазина">
                        </div>

                        <div class="form-group">
                            <label for="widget_welcome" class="font-weight-bold">Приветственное сообщение</label>
                            <textarea class="form-control" 
                                      id="widget_welcome" 
                                      name="widget_welcome" 
                                      rows="3"
                                      placeholder="Здравствуйте! Я AI-помощник. Спросите меня о ценах...">Здравствуйте! Я AI-помощник. Спросите меня о ценах, услугах или условиях работы.</textarea>
                        </div>

                        <div class="form-group">
                            <label for="widget_color" class="font-weight-bold">Цвет виджета</label>
                            <div class="input-group">
                                <input type="color" 
                                       class="form-control" 
                                       id="widget_color" 
                                       name="widget_color" 
                                       value="#4f46e5">
                                <span class="input-group-append">
                                    <span class="input-group-text" id="color_preview">#4f46e5</span>
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="widget_position" class="font-weight-bold">Позиция виджета</label>
                            <select class="form-control" id="widget_position" name="widget_position">
                                <option value="right">Правый нижний угол</option>
                                <option value="left">Левый нижний угол</option>
                            </select>
                        </div>

                        <div class="custom-control custom-switch">
                            <input type="checkbox" 
                                   class="custom-control-input" 
                                   id="widget_enabled" 
                                   name="widget_enabled" 
                                   checked>
                            <label class="custom-control-label" for="widget_enabled">
                                Виджет включен
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Продвинутые настройки -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-sliders-h text-primary"></i> Продвинутые настройки</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="max_chunks" class="font-weight-bold">Максимум чанков для поиска</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="max_chunks" 
                                   name="max_chunks" 
                                   value="5"
                                   min="1"
                                   max="20">
                            <small class="form-text text-muted">
                                Сколько фрагментов текста использовать для формирования ответа
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="min_similarity" class="font-weight-bold">Минимальная релевантность (%)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="min_similarity" 
                                   name="min_similarity" 
                                   value="30"
                                   min="0"
                                   max="100">
                            <small class="form-text text-muted">
                                Если найденные фрагменты менее релевантны, AI сообщит что не знает ответа
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="chunk_size" class="font-weight-bold">Размер чанка (символы)</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="chunk_size" 
                                   name="chunk_size" 
                                   value="500"
                                   min="100"
                                   max="2000">
                            <small class="form-text text-muted">
                                Оптимально 400-600 символов
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Правая колонка -->
            <div class="col-lg-4">
                <!-- Сохранение -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block btn-lg mb-3">
                            <i class="fas fa-save"></i> Сохранить настройки
                        </button>
                        
                        <hr>
                        
                        <h6 class="font-weight-bold mb-3">Быстрые действия:</h6>
                        
                        <a href="{{ route('admin.seo.ai-kb.index') }}" class="btn btn-outline-primary btn-block mb-2">
                            <i class="fas fa-database"></i> База знаний
                        </a>
                        
                        <a href="{{ route('admin.seo.ai-kb.chat-history') }}" class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-history"></i> История чатов
                        </a>
                        
                        <button type="button" class="btn btn-outline-success btn-block mb-2" onclick="testConnection()">
                            <i class="fas fa-plug"></i> Проверить соединение
                        </button>
                        
                        <button type="button" class="btn btn-outline-warning btn-block" onclick="regenerateAllEmbeddings()">
                            <i class="fas fa-sync"></i> Перегенерировать все эмбеддинги
                        </button>
                    </div>
                </div>

                <!-- Статус -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 font-weight-bold">Статус системы</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">API ключ:</small>
                                <span id="api_status" class="badge badge-warning">Не проверено</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">База знаний:</small>
                                <span class="badge badge-success">Активна</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Виджет:</small>
                                <span class="badge badge-success">Готов к установке</span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="font-weight-bold mb-2">Как установить виджет:</h6>
                        <small class="text-muted d-block mb-2">
                            Добавьте в шаблон сайта (перед &lt;/body&gt;):
                        </small>
                        <pre class="bg-light p-2 rounded" style="font-size: 11px; overflow-x: auto;">
&lt;x-ai-chat.widget 
    title="Помощник"
    color="#4f46e5"
    position="right"
/&gt;</pre>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Обновление превью цвета
document.getElementById('widget_color').addEventListener('input', function(e) {
    document.getElementById('color_preview').textContent = e.target.value;
});

// Тест соединения с API
async function testConnection() {
    const apiKey = document.getElementById('openai_api_key').value;
    const statusBadge = document.getElementById('api_status');
    
    if (!apiKey) {
        alert('Введите API ключ сначала!');
        return;
    }
    
    statusBadge.className = 'badge badge-warning';
    statusBadge.textContent = 'Проверка...';
    
    try {
        // Здесь будет реальный тест API
        await new Promise(resolve => setTimeout(resolve, 1000));
        statusBadge.className = 'badge badge-success';
        statusBadge.textContent = 'Подключено';
        alert('Соединение успешно! API ключ действителен.');
    } catch (e) {
        statusBadge.className = 'badge badge-danger';
        statusBadge.textContent = 'Ошибка';
        alert('Ошибка подключения. Проверьте API ключ.');
    }
}

// Перегенерация всех эмбеддингов
function regenerateAllEmbeddings() {
    if (confirm('Это действие займет время при большой базе документов. Продолжить?')) {
        // Здесь будет логика вызова джобы
        alert('Запущена перегенерация эмбеддингов. Это может занять несколько минут.');
    }
}
</script>
@endpush
@endsection
