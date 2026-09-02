# Настройка webhook'ов в n8n для VertexCMS Chatbot

## Обзор

VertexCMS отправляет webhook'и в n8n при следующих событиях:
- `message_received` - получено сообщение от пользователя
- `form_submitted` - пользователь заполнил форму в чате
- `lead_captured` - собран лид (контактные данные)
- `ticket_created` - создан тикет поддержки

## Структура payload

### message_received
```json
{
  "event": "message_received",
  "chatbot_id": 1,
  "chatbot_slug": "vertex-assistant",
  "session_id": "uuid-session-id",
  "user_id": null,
  "message": {
    "id": 123,
    "content": "Какая цена на ваши услуги?",
    "timestamp": "2024-01-02T10:00:00Z"
  },
  "context": {
    "page_uri": "/services",
    "page_title": "Наши услуги",
    "conversation_history": [...]
  }
}
```

### form_submitted
```json
{
  "event": "form_submitted",
  "chatbot_id": 1,
  "session_id": "uuid-session-id",
  "form_data": {
    "full_name": "Иван Петров",
    "email": "ivan@example.com",
    "phone": "+7 999 123-45-67",
    "budget": "$5000-$10000"
  },
  "context": {
    "page_uri": "/contact",
    "page_title": "Контакты"
  }
}
```

## Примеры workflow в n8n

### 1. Обработка вопросов о ценах

**Триггер:** Webhook node
- Method: POST
- Path: `/vertexcms/chatbot/webhook`
- Authentication: Header Auth (токен из `.env`)

**Логика:**
1. **IF Node**: Проверить, содержит ли сообщение ключевые слова
   - Expression: `{{ $json.message.content.toLowerCase().includes('цена') || $json.message.content.toLowerCase().includes('стоимость') }}`
   
2. **HTTP Request Node**: Запрос к базе данных товаров/услуг
   - GET запрос к вашей CRM или базе данных
   
3. **Function Node**: Форматирование ответа
   ```javascript
   return {
     response: {
       text: `Стоимость наших услуг:\n${$input.item.json.services.map(s => `- ${s.name}: ${s.price}`).join('\n')}\n\nХотите записаться на консультацию?`,
       show_form: true,
       form_type: 'consultation_request'
     }
   };
   ```

4. **HTTP Request Node**: Отправка ответа обратно в VertexCMS
   - POST на `https://your-cms.com/api/ai/webhooks/response`
   - Body: `{{ $json.response }}`

### 2. Сбор лидов в CRM

**Триггер:** Webhook node (event: `form_submitted`)

**Логика:**
1. **Google Sheets Node** или **Airtable Node**: Сохранение лида
   - Add row с данными из `form_data`
   
2. **Email Node**: Отправка уведомления менеджеру
   - To: `{{ env('SALES_EMAIL') }}`
   - Subject: `Новый лид от {{ $json.form_data.full_name }}`
   - Body: Детали лида
   
3. **Slack Node** или **Telegram Node**: Уведомление в чат продаж
   - Message: `🎯 Новый лид: {{ $json.form_data.full_name }} ({{ $json.form_data.email }})`
   
4. **HTTP Request Node**: Ответ пользователю
   ```javascript
   return {
     response: {
       text: `Спасибо, ${$json.form_data.full_name}! Мы свяжемся с вами в течение 24 часов.`,
       close_chat: false
     }
   };
   ```

### 3. Создание тикета поддержки

**Триггер:** Webhook node (event: `message_received`)

**Логика:**
1. **IF Node**: Проверка сложности вопроса
   - Использовать AI Node для классификации
   
2. **HTTP Request Node**: Создание тикета в HelpScout/Zendesk
   - POST на API тикет-системы
   - Body: `{ "subject": "...", "description": "...", "customer_email": "..." }`
   
3. **Wait Node**: Пауза 2 секунды
   
4. **HTTP Request Node**: Ответ пользователю с номером тикета
   ```javascript
   return {
     response: {
       text: `Я создал тикет #${$json.ticket.id} для вашего вопроса. Наша команда ответит в ближайшее время.`,
       ticket_id: $json.ticket.id
     }
   };
   ```

## Настройка в .env

Добавьте в файл `.env`:

```bash
# n8n Webhook URLs
N8N_BASE_URL=https://your-n8n-instance.com
N8N_WEBHOOK_PATH=/webhook/vertexcms-chatbot
N8N_SUPPORT_WEBHOOK_URL=${N8N_BASE_URL}${N8N_WEBHOOK_PATH}/support
N8N_LEAD_WEBHOOK_URL=${N8N_BASE_URL}${N8N_WEBHOOK_PATH}/leads

# n8n Authentication
N8N_WEBHOOK_TOKEN=your-secret-token-here
```

## Тестирование webhook'ов

1. Используйте **n8n Editor** для просмотра входящих запросов
2. Включите **Execute Workflow** для тестирования
3. Проверьте логи в VertexCMS: `storage/logs/laravel.log`

## Безопасность

- Всегда используйте HTTPS для webhook'ов
- Настройте аутентификацию через Header Auth в n8n
- Проверяйте подпись webhook в production
- Ограничьте IP адреса в настройках n8n

