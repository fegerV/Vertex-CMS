# Модульная система AI-чатботов для VertexCMS

## Реализованные компоненты (Фаза 1)

### ✅ Middleware PageContext
**Файл:** `app/Http/Middleware/PageContextMiddleware.php`
- Автоматически извлекает контекст страницы (URI, title, excerpt, metadata)
- Добавляет контекст в запросы к AI чату
- Поддерживает загрузку данных страницы из БД
- **Зарегистрирован в:** `bootstrap/app.php`

### ✅ Модель Chatbot
**Файл:** `app/Models/Chatbot.php`
- Модульная архитектура с независимыми ботами
- Настройки LLM (провайдер, модель, температура, лимиты токенов)
- Системные промпты и конфигурация поведения
- Rate limiting (сообщения/минуту, сообщения/час, токены/день)
- UI конфигурация (цвета, позиции, аватарки)
- Webhook интеграция для n8n
- Привязка к страницам и ролям пользователей

### ✅ Seeders для разработки
**Файлы:** 
- `database/seeders/ChatbotSeeder.php` - создание демо-ботов
- `database/seeders/DatabaseSeeder.php` - обновлён с вызовом ChatbotSeeder

**Создаются 3 демо-бота:**
1. **Vertex Assistant** - универсальный помощник (default)
2. **Support Bot** - техническая поддержка
3. **Lead Generator** - сбор лидов с формами

### ✅ Обновлённый виджет чата
**Файл:** `resources/views/components/ai-chat/widget.blade.php`
- Автоматическая загрузка настроек из модели Chatbot
- Поддержка multiple chatbots через параметр `chatbotSlug`
- Динамические цвета, аватарки, приветственные сообщения
- Адаптивный UI с toggle отображения аватара

### ✅ Документация по n8n интеграции
**Файл:** `docs/n8n-webhook-examples.md`
- Структура payload для webhook'ов
- Примеры workflow для обработки цен, сбора лидов, создания тикетов
- Инструкция по настройке .env
- Best practices по безопасности

## Как использовать

### 1. Запустить сидеры (разработка)
```bash
php artisan db:seed --class=ChatbotSeeder
```

Или все сидеры:
```bash
php artisan db:seed
```

### 2. Использование виджета на странице

**Базовое использование (default бот):**
```blade
<x-ai-chat.widget />
```

**Конкретный бот по slug:**
```blade
<x-ai-chat.widget chatbot-slug="support-bot" />
```

**Переопределение настроек:**
```blade
<x-ai-chat.widget 
    chatbot-slug="lead-generator"
    :showAvatar="true"
    avatarUrl="/images/sales-avatar.png"
/>
```

### 3. Frontend: отправка контекста страницы

Добавьте в JavaScript вашего сайта:
```javascript
// При инициализации чата
const pageContext = {
    page_uri: window.location.pathname,
    page_title: document.title,
    page_excerpt: document.querySelector('meta[name="description"]')?.content || '',
    page_id: window.vertexPageId // если доступно
};

// Отправка вместе с сообщением
fetch('/api/ai/chat/messages', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-Page-Uri': pageContext.page_uri,
        'X-Page-Title': pageContext.page_title,
        'X-Page-Excerpt': pageContext.page_excerpt,
        'X-Page-Metadata': JSON.stringify(pageContext)
    },
    body: JSON.stringify({
        message: 'Ваш вопрос',
        chatbot_slug: 'vertex-assistant' // опционально
    })
});
```

### 4. Настройка n8n webhook'ов

**В .env:**
```bash
N8N_BASE_URL=https://your-n8n-instance.com
N8N_WEBHOOK_PATH=/webhook/vertexcms-chatbot
N8N_SUPPORT_WEBHOOK_URL=${N8N_BASE_URL}${N8N_WEBHOOK_PATH}/support
N8N_LEAD_WEBHOOK_URL=${N8N_BASE_URL}${N8N_WEBHOOK_PATH}/leads
N8N_WEBHOOK_TOKEN=your-secret-token-here
```

**Обновить URL в базе данных:**
```sql
UPDATE chatbots SET webhook_url = 'https://your-n8n.com/webhook/...' WHERE slug = 'lead-generator';
```

## Архитектура

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────┐
│   Frontend      │────▶│  PageContext     │────▶│ Chatbot     │
│   (Widget)      │     │  Middleware      │     │ Service     │
└─────────────────┘     └──────────────────┘     └─────────────┘
                               │                       │
                               ▼                       ▼
                        ┌─────────────┐         ┌─────────────┐
                        │ Page Data   │         │ n8n Webhook │
                        │ (DB/Cache)  │         │ (Automation)│
                        └─────────────┘         └─────────────┘
```

## Следующие шаги (Фаза 2)

1. **Rules Engine** - система условий/действий
2. **Webhook отправка в n8n** при событиях
3. **Интерактивные формы** в чате
4. **Админ-панель** для управления ботами

## Тестирование

1. Проверить загрузку виджета: `/` (должен загрузиться default бот)
2. Проверить контекст страницы: открыть любую страницу, задать вопрос о ней
3. Проверить разные боты: `<x-ai-chat.widget chatbot-slug="support-bot" />`
4. Проверить логи: `storage/logs/laravel.log`

## Примечания

- Миграции не применялись (режим разработки)
- Таблицы создадутся автоматически при использовании SQLite/Laravel
- Для production применить миграции: `php artisan migrate`
