# API Strategy

## Цель

API VertexCMS должен обслуживать три сценария:

- админ-панель, если часть интерфейса работает как SPA;
- мобильные приложения;
- внешние интеграции и будущий marketplace.

API нужно проектировать как стабильный публичный контракт, а не как прямой доступ к внутренним Eloquent-моделям.

## Базовая структура

```txt
/api/v1/public/*
/api/v1/auth/*
/api/v1/me
/api/v1/pages
/api/v1/media
/api/v1/menus
/api/v1/settings
/api/v1/taxonomies
```

Admin API может оставаться под отдельным prefix:

```txt
/admin/api/*
```

## Аутентификация

Рекомендуемый подход для MVP и мобильных приложений:

- Laravel Sanctum для personal access tokens.
- Session auth для web admin.
- Bearer tokens для mobile/API clients.
- Отдельные application tokens для серверных интеграций.

Типы токенов:

- `user_token` - выдан пользователю, работает с permissions пользователя.
- `app_token` - выдан приложению, имеет ограниченный scope.
- `ai_provider_key` - ключ внешней нейросети, хранится отдельно и не используется как auth token VertexCMS.

## Scopes

Начальный набор scopes:

- `content:read`
- `content:write`
- `media:read`
- `media:write`
- `settings:read`
- `settings:write`
- `seo:read`
- `seo:write`
- `ai:use`
- `system:read`

## Mobile API

Мобильному приложению нужны endpoints, которые не зависят от HTML-админки:

- получить список опубликованных страниц;
- получить страницу по URI;
- получить меню по location;
- получить данные сайта;
- получить media metadata;
- получить профиль пользователя;
- обновить профиль пользователя;
- загрузить медиа, если есть права;
- получить taxonomy terms и архивы, когда taxonomy будет включена.

Пример:

```txt
GET /api/v1/public/pages?status=published
GET /api/v1/public/pages/by-uri?uri=/about
GET /api/v1/public/menus/header
GET /api/v1/public/settings/site
GET /api/v1/me
```

## Формат ответа

Успешный ответ:

```json
{
  "data": {
    "id": 1,
    "title": "About"
  },
  "meta": {
    "api_version": "v1"
  }
}
```

Коллекция:

```json
{
  "data": [],
  "links": {
    "first": null,
    "last": null,
    "prev": null,
    "next": null
  },
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 0,
    "api_version": "v1"
  }
}
```

Ошибка:

```json
{
  "error": {
    "code": "forbidden",
    "message": "You do not have permission to perform this action.",
    "details": {}
  }
}
```

## API Resources

API не должен возвращать Eloquent-модели напрямую.

Нужны resource-классы:

- `PageResource`
- `PageCollection`
- `MediaResource`
- `MenuResource`
- `SettingResource`
- `UserResource`
- `TaxonomyResource`
- `TermResource`

## Rate limiting

Базовые лимиты:

- public API: 120 requests/minute/IP;
- authenticated API: 300 requests/minute/user;
- AI endpoints: отдельный лимит по пользователю и provider budget;
- login endpoints: 5 попыток / 15 минут.

## OpenAPI

К версии `v0.2` нужно добавить OpenAPI schema:

```txt
docs/api/openapi.yaml
```

Acceptance criteria:

- каждый публичный endpoint описан;
- описаны auth schemes;
- описаны error codes;
- описаны request/response schemas;
- mobile app может генерировать typed client из схемы.

## Security

- Все write endpoints требуют auth и permissions.
- Все входные данные проходят request validation.
- Tokens хранятся в hashed виде.
- Application tokens имеют scopes.
- CORS включается явно через настройки.
- API не раскрывает внутренние пути файлов и stack traces.
- Media download не должен обходить permissions для приватных файлов.
