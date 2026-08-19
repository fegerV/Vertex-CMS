# AI Module

## Цель

AI Module должен дать редактору встроенного помощника для создания страниц, текстов, SEO-описаний и базового оформления без превращения CMS в зависимость от одного провайдера нейросетей.

## Принципы

- VertexCMS не привязывается к одному AI provider.
- API keys хранятся зашифрованно.
- AI не сохраняет изменения без подтверждения пользователя.
- Все AI-действия логируются.
- Доступ к AI управляется permissions.
- AI работает с JSON-структурой страницы, а не с финальным HTML.

## Провайдеры

В системе должен быть provider registry.

Минимальные поля provider:

```txt
id
name
slug
base_url
status
default_model
settings_json
created_at
updated_at
```

Минимальные поля AI key:

```txt
id
provider_id
name
encrypted_key
status
created_by
last_used_at
created_at
updated_at
```

Ключи нельзя показывать повторно после сохранения.

## Permissions

Новые permissions:

```txt
ai.view
ai.use
ai.manage_providers
ai.manage_keys
ai.view_logs
```

Роли по умолчанию:

- Super Admin может управлять провайдерами и ключами.
- Admin может использовать AI и смотреть логи.
- Editor может использовать AI, если permission выдан явно.
- Viewer не может использовать AI.

## Настройки

Раздел:

```txt
/admin/settings/ai
```

Настройки:

```txt
ai.enabled
ai.default_provider
ai.default_model
ai.monthly_budget
ai.store_prompts
ai.store_responses
ai.allow_editor_use
ai.content_language
ai.brand_voice
```

## AI chat на странице создания страницы

В интерфейсе создания и редактирования страницы нужна боковая панель чата.

Расположение:

- страница `/admin/pages/create`;
- страница `/admin/pages/{id}/edit`;
- builder `/admin/pages/{id}/builder`.

Что умеет чат:

- предложить структуру страницы;
- написать hero-заголовок;
- написать текстовый блок;
- переписать текст проще, официальнее, короче или продающе;
- создать FAQ;
- предложить CTA;
- предложить SEO title;
- предложить SEO description;
- предложить Open Graph title и description;
- предложить alt для выбранного изображения;
- предложить JSON-блоки для builder.

## UX-поведение

- AI-ответ показывается как draft.
- Пользователь выбирает: вставить в выбранный блок, заменить текст, добавить новый блок или отклонить.
- Для SEO-полей AI предлагает варианты, но не публикует их автоматически.
- Для структуры страницы AI возвращает блоки в builder schema.
- Если ответ содержит неподдерживаемый block type, UI показывает предупреждение и предлагает заменить на ближайший поддерживаемый блок.

## API endpoints

```txt
GET /admin/api/ai/providers
POST /admin/api/ai/providers
PUT /admin/api/ai/providers/{provider}
DELETE /admin/api/ai/providers/{provider}

POST /admin/api/ai/keys
DELETE /admin/api/ai/keys/{key}

POST /admin/api/ai/chat
POST /admin/api/ai/suggest/page-structure
POST /admin/api/ai/suggest/seo
POST /admin/api/ai/suggest/block
POST /admin/api/ai/rewrite
```

## Request context

AI-запрос должен получать только нужный минимум данных:

```json
{
  "page": {
    "title": "Услуги",
    "uri": "/services",
    "status": "draft"
  },
  "seo": {
    "title": null,
    "description": null
  },
  "builder": {
    "selected_block": {},
    "schema_version": "1.0"
  },
  "instruction": "Напиши SEO description"
}
```

## Безопасность

- API keys шифруются через Laravel encryption.
- Ключи не пишутся в логи.
- Prompt и response можно хранить только при включённой настройке.
- HTML из AI-ответа проходит sanitization.
- Для HTML-блока нужно ограничение: не Super Admin не может вставлять script.
- AI endpoints ограничиваются rate limit.
- Нужно считать token usage или хотя бы количество запросов.

## Activity logs

Логировать:

- создание provider;
- добавление key;
- удаление key;
- AI chat request;
- вставку AI-текста в страницу;
- генерацию SEO;
- ошибку provider.

## Acceptance criteria

- Super Admin может добавить и удалить AI key.
- Editor с `ai.use` видит AI chat на странице создания страницы.
- AI предлагает текст без автоматического сохранения.
- AI может предложить SEO title и description.
- AI может предложить builder JSON, совместимый со schema version.
- Все AI-запросы логируются без раскрытия secret key.
