# Модульная система AI-чатботов для VertexCMS

## Обзор

Реализована модульная архитектура AI-ассистентов с поддержкой множественных ботов, контекста страницы, правил автоматизации и интеграции с n8n.

## Компоненты системы

### 1. Модель Chatbot
**Файл:** `app/Models/Chatbot.php`

Основные возможности:
- Индивидуальные настройки LLM (провайдер, модель)
- Системные промпты с поддержкой page context
- Лимиты (rate limiting)
- Capabilities toggles (веб-поиск, генерация изображений, голосовой ввод, загрузка файлов)
- Ограничения по страницам и ролям
- Webhook интеграции для n8n

### 2. Модель ChatbotRule
**Файл:** `app/Models/ChatbotRule.php`

Движок правил: Событие → Условие → Действие

Типы условий:
- `equals`, `not_equals` - точное совпадение
- `contains`, `not_contains` - содержит/не содержит
- `starts_with`, `ends_with` - начинается/заканчивается
- `regex` - регулярные выражения
- `is_empty`, `is_not_empty` - проверка на пустоту
- `greater_than`, `less_than` - числовые сравнения

Типы действий:
- `webhook` - вызов внешнего API (n8n)
- `show_form` - отображение формы в чате
- `block_llm` - блокировка LLM с кастомным ответом
- `set_context` - установка переменных контекста

### 3. Сервис ModularChatbotService
**Файл:** `app/Services/AI/ModularChatbotService.php`

Обрабатывает сообщения через:
1. Проверку конфигурации бота
2. Rate limiting
3. Создание сессии с page context
4. Проверку правил (Rules Engine)
5. Построение промпта с контекстом
6. Поиск в базе знаний (RAG)
7. Вызов LLM
8. Сохранение истории

### 4. Middleware PageContextMiddleware
**Файл:** `app/Http/Middleware/PageContextMiddleware.php`

Автоматически извлекает контекст страницы из:
- HTTP заголовков (`X-Page-Uri`, `X-Page-Title`, `X-Page-Excerpt`)
- Параметров запроса
- Базы данных (по page_id)

## Миграции

### 1. Таблица chatbots
```bash
php artisan migrate --path=database/migrations/2024_01_02_000000_create_chatbots_table.php
```

Поля:
- `name`, `slug` - идентификация
- `provider`, `model` - настройки LLM
- `system_prompt`, `use_page_context`, `use_knowledge_base` - промпты
- `max_tokens_per_message`, `rate_limit_per_minute/hour` - лимиты
- `enable_web_search`, `enable_image_generation`, etc. - capabilities
- `webhook_url`, `webhook_triggers` - интеграции

### 2. Таблица chatbot_rules
```bash
php artisan migrate --path=database/migrations/2024_01_02_000001_create_chatbot_rules_table.php
```

### 3. Обновление ai_chat_sessions
```bash
php artisan migrate --path=database/migrations/2024_01_02_000002_update_ai_chat_sessions.php
```

Добавляет:
- `chatbot_id` - привязка к боту
- `page_uri`, `page_title`, `page_excerpt`, `page_metadata` - контекст страницы
- `user_id` - привязка к пользователю

## API Endpoints

### POST /api/ai/chat
Отправить сообщение боту

**Request:**
```json
{
  "message": "Сколько стоит доставка?",
  "session_id": "uuid-сессии",
  "chatbot_id": 1,
  "page_context": {
    "uri": "/delivery",
    "title": "Доставка и оплата",
    "excerpt": "Информация о доставке товаров"
  },
  "user_context": {
    "user_id": 123
  }
}
```

**Response:**
```json
{
  "success": true,
  "answer": "Доставка стоит 500 руб...",
  "sources": [...],
  "confidence": 0.9,
  "session_id": "uuid-сессии",
  "chatbot_id": 1,
  "chatbot_name": "Support Bot",
  "tokens_used": 150,
  "rule_triggered": false,
  "form_schema": null,
  "webhook_data": null
}
```

### GET /api/ai/chat/bots
Получить доступных ботов для страницы

**Request:**
```
GET /api/ai/chat/bots?uri=/products
```

### POST /api/ai/chat/session
Создать новую сессию

## Примеры использования

### 1. Создание бота через код
```php
$chatbot = Chatbot::create([
    'name' => 'Support Assistant',
    'slug' => 'support-bot',
    'description' => 'Бот для поддержки клиентов',
    'provider' => 'openai',
    'model' => 'gpt-4',
    'system_prompt' => 'Ты помощник службы поддержки...',
    'use_page_context' => true,
    'use_knowledge_base' => true,
    'max_tokens_per_message' => 500,
    'rate_limit_per_minute' => 10,
    'enable_web_search' => false,
    'enable_file_upload' => true,
    'webhook_url' => 'https://n8n.example.com/webhook/chatbot',
]);
```

### 2. Создание правила с webhook
```php
ChatbotRule::create([
    'chatbot_id' => $chatbot->id,
    'name' => 'Запрос цены',
    'event_type' => 'message_received',
    'conditions' => [
        [
            'field' => 'message',
            'operator' => 'contains',
            'value' => 'цена'
        ],
        [
            'field' => 'message',
            'operator' => 'regex',
            'value' => '/стоимость|прайс|сколько стоит/i'
        ]
    ],
    'actions' => [
        [
            'type' => 'webhook',
            'url' => 'https://n8n.example.com/webhook/get-price',
            'method' => 'POST'
        ]
    ],
    'priority' => 10,
    'stop_on_match' => true,
    'is_active' => true,
]);
```

### 3. Создание правила с формой
```php
ChatbotRule::create([
    'chatbot_id' => $chatbot->id,
    'name' => 'Заказ звонка',
    'event_type' => 'message_received',
    'conditions' => [
        [
            'field' => 'message',
            'operator' => 'contains',
            'value' => 'заказать звонок'
        ]
    ],
    'actions' => [
        [
            'type' => 'show_form',
            'form_schema' => [
                'fields' => [
                    [
                        'name' => 'name',
                        'label' => 'Ваше имя',
                        'type' => 'text',
                        'required' => true
                    ],
                    [
                        'name' => 'phone',
                        'label' => 'Телефон',
                        'type' => 'tel',
                        'required' => true
                    ],
                    [
                        'name' => 'budget',
                        'label' => 'Бюджет',
                        'type' => 'select',
                        'options' => ['до 10к', '10-50к', '50-100к', 'более 100к']
                    ]
                ]
            ]
        ]
    ],
    'priority' => 5,
]);
```

### 4. Фронтенд интеграция
```javascript
// Отправка сообщения с контекстом страницы
async function sendMessage(message, sessionId) {
    const response = await fetch('/api/ai/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Page-Uri': window.location.pathname,
            'X-Page-Title': document.title,
        },
        body: JSON.stringify({
            message: message,
            session_id: sessionId,
            chatbot_slug: 'support-bot',
            page_context: {
                uri: window.location.pathname,
                title: document.title,
                excerpt: getMetaDescription()
            }
        })
    });
    
    return await response.json();
}

// Получение доступных ботов
async function getAvailableBots() {
    const response = await fetch(`/api/ai/chat/bots?uri=${encodeURIComponent(window.location.pathname)}`);
    const data = await response.json();
    return data.bots;
}
```

## Интеграция с n8n

### Пример webhook payload
```json
{
  "chatbot_id": 1,
  "rule_id": 5,
  "rule_name": "Запрос цены",
  "message": "Сколько стоит доставка в Москву?",
  "timestamp": "2024-01-15T10:30:00+03:00",
  "page_uri": "/delivery",
  "page_title": "Доставка и оплата",
  "session_id": "uuid-сессии",
  "user_id": 123
}
```

### Пример ответа от n8n
```json
{
  "response": "Доставка в Москву стоит 500 руб. Срок доставки 3-5 дней.",
  "data": {
    "city": "Москва",
    "price": 500,
    "delivery_time": "3-5 дней"
  }
}
```

## Рекомендации по внедрению

### Фаза 1 (MVP) - Завершено ✅
- [x] Модульная архитектура (Chatbot модель)
- [x] Page Context middleware
- [x] Базовый Rules Engine
- [x] Интеграция в контроллер ApiChatApiController
- [x] Seeders для демо-ботов
- [x] Обновление виджета с поддержкой page context

### Фаза 2 - Реализовано ✅
- [x] Webhook интеграция с n8n
- [x] Расширенные условия правил (regex, числовые сравнения)
- [x] Rate limiting на уровне сервиса
- [x] Интерактивные формы в чате (frontend + backend)
- [x] Поддержка множественных ботов на странице

### Фаза 3 - Готово к тестированию
- [ ] Загрузка файлов (требует доработки UI)
- [ ] Голосовой ввод/вывод (Web Speech API)
- [ ] Генерация изображений (DALL-E integration)

### Фаза 4 - Требуется разработка
- [ ] Админ-панель для управления ботами
- [ ] Визуальный конструктор правил (drag-and-drop)
- [ ] Аналитика и мониторинг (дашборды)
- [ ] A/B тестирование промптов

## Производительность

### Оптимизация
- Кэширование конфигурации ботов
- Lazy loading базы знаний
- Асинхронная обработка webhook'ов
- Rate limiting на уровне Redis

### Мониторинг
- Логирование ошибок в Laravel Log
- Метрики использования токенов
- Время ответа LLM
- Статистика срабатывания правил

## Безопасность

- Валидация всех входных данных
- Проверка прав доступа для админ-функций
- Sanitization пользовательского ввода
- HTTPS для webhook'ов
- Rate limiting для защиты от злоупотреблений
