# Реализация недостающих функций (P0 MVP)

## Дата: 2026-05-07

## Обзор
Реализованы критически важные для MVP функции, которые были указаны в PLAN нереализованных функций. Все изменения сделаны с соблюдением архитектурных паттернов и code-style проекта.

---

## 1. API v1 Публичные эндпоинты `/api/v1/public/*` ✅

### Изменения:
- **`routes/public_api.php`** — Обновлён для поддержки полного набора публичных эндпоинтов
- **`app/Content/Http/Controllers/FrontendPageApiController.php`** — Создан новый контроллер
- **`app/Content/Http/Resources/PageResource.php`** — Создан API ресурс для стандартизированных ответов

### Реализованные эндпоинты:
| Метод | Эндпоинт | Описание |
|-------|----------|----------|
| GET | `/api/v1/public/pages` | Список опубликованных страниц (пагинация) |
| GET | `/api/v1/public/pages/{page}` | Получение конкретной страницы по ID |
| GET | `/api/v1/public/pages/by-uri/{uri}` | Получение страницы по URI (без слэша) |
| GET | `/api/v1/public/menus/{location}` | Меню по локации |
| GET | `/api/v1/public/settings/site` | Публичные настройки сайта |
| GET | `/api/v1/public/sitemap.xml` | XML карта сайта |
| GET | `/api/v1/public/robots.txt` | Файл robots.txt |

### Особенности:
- Фильтрация по статусу `published` и дате публикации
- Автоматическое включение SEO-данных при наличии
- Поддержка родительских страниц
- Формат даты ISO 8601

---

## 2. Page API Контроллер — Реальная бизнес-логика ✅

### Изменения:
- **`app/Content/Http/Controllers/PageApiController.php`** — Полностью переписан

### Что сделано:
- Интеграция с `PageService` (создание, обновление, удаление)
- Полная валидация входящих данных
- Автоматическое создание slug и URI
- Управление версиями (ревизии)
- SEO метаданные через `SeoMetaService`
- Корректные HTTP статусы и формат ошибок валидации (422)
- Ресурсные JSON-ответы через `PageResource`

### Валидация включает:
- `title`, `slug`, `status`, `template`, `content_json`
- Все SEO поля: `seo_title`, `seo_description`, `seo_canonical_url`, `seo_robots`
- OG теги: `seo_og_title`, `seo_og_description`, `seo_og_image`
- `seo_schema_json`, `seo_include_in_sitemap`
- `published_at` для запланированных публикаций

---

## 3. Redirect API — Реальная логика ✅

### Изменения:
- **`app/Seo/Http/Controllers/RedirectController.php`** — Реализованы CRUD операции

### Методы:
- `index()` — Пагинированный список редиректов
- `store()` — Создание с валидацией уникальности `from_url`
- `show()` — Получение одного редиректа
- `update()` — Обновление (поддержка частичного обновления)
- `destroy()` — Удаление

### Валидация:
- `from_url`: обязательный, уникальный, максимум 500 символов
- `to_url`: обязательный, максимум 500 символов
- `status_code`: 301, 302, 307 или 308
- `comment`: опционально, максимум 500 символов
- `is_active`: булево значение

---

## 4. Builder API — Блоки и превью-рендеринг ✅

### Изменения:
- **`app/Builder/Http/Controllers/BuilderApiController.php`** — Расширен функционал

### Эндпоинты:
- `GET /api/builder/blocks` — Список доступных блоков с конфигурацией по умолчанию
- `POST /api/builder/render-preview` — Рендеринг превью JSON-контента

### Поддерживаемые блоки:
- `heading` — Заголовок (H1-H6)
- `text` — Текстовый блок
- `button` — Кнопка с ссылкой
- `divider` — Разделитель
- `faq` — FAQ (вопрос-ответ)
- `html` — HTML код (с санитизацией)
- `image` — Изображение (через Media)

### Возвращаемые данные для каждого блока:
- `name` — Читаемое имя
- `icon` — Иконка
- `default` — Объект с настройками по умолчанию

---

## 5. Настройки (Settings) API — Меню ✅

### Изменения:
- **`app/System/Http/Controllers/PublicSettingsApiController.php`** — Добавлен метод `menu()`

### Эндпоинт:
- `GET /api/v1/public/menus/{location}` — Возвращает меню по локации

### Возвращаемые данные:
- `id` — ID пункта меню
- `label` — Текст ссылки
- `url` — URL
- `target` — target атрибут (_self, _blank)
- `parent_id` — Родительский элемент
- `sort_order` — Порядок сортировки

---

## 6. Конфигурация проекта ✅

### Изменения:
- **`config/vertex.php`** — Добавлены секции `api` и `seo`
- **`.env.example`** — Добавлены переменные для PWA и AI модулей

### Новые настройки:
```php
'api' => [
    'public_enabled' => env('API_PUBLIC_ENABLED', true),
    'version' => 'v1',
    'rate_limit' => [
        'public' => 60,
        'authenticated' => 120,
    ],
],
'seo' => [
    'auto_generate_meta' => true,
    'default_robots' => 'index, follow',
    'default_title_suffix' => ' | VertexCMS',
],
```

### Новые переменные окружения:
- `API_PUBLIC_ENABLED=true`
- PWA: `PWA_ENABLED`, `PWA_THEME_COLOR`, `PWA_DISPLAY` и др.
- AI: `AI_ENABLED`, `AI_PROVIDER`, `AI_API_KEY`, `AI_MODEL`

---

## Архитектурные решения

### 1. RESTful API Design
- Использованы стандартные HTTP методы: GET, POST, PUT/PATCH, DELETE
- Корректные HTTP статусы: 200, 201, 404, 422, 403
- JSON формат запросов и ответов

### 2. Ресурсные трансформации
- `PageResource` обеспечивает единый формат ответов
- Автоматическое скрытие null-значений
- Вложенные объекты (SEO, родитель) только при их наличии

### 3. Валидация и безопасность
- Все входные данные валидируются через FormRequest-подобный подход
- Защита от XSS в HTML блоках (strip_tags, preg_replace)
- Защита от произвольного JavaScript в SVG
- Rate limiting на уровне middleware (Laravel)

### 4. Производительность
- Eager loading отношений (`with('seoMeta')`)
- Пагинация (по 50 элементов на страницу для API)
- Кэширование настроек и меню через SettingsService

### 5. Согласованность
- Соответствие PSR-12 coding standards
- Единый стиль именования: camelCase для методов, snake_case для JSON
- Использование DTO-подобных паттернов (Resources)
- Соответствие Laravel best practices

---

## Тестирование (Рекомендации)

### Для проверки реализованного функционала:

1. **Pages CRUD API**
```bash
# Список страниц
curl http://localhost/api/pages

# Создать страницу
curl -X POST http://localhost/api/pages \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","slug":"test","status":"published","content_json":{...}}'

# Обновить
curl -X PUT http://localhost/api/pages/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated"}'
```

2. **Public API**
```bash
# Опубликованные страницы
curl http://localhost/api/v1/public/pages

# По URI
curl http://localhost/api/v1/public/pages/by-uri/about

# Настройки
curl http://localhost/api/v1/public/settings/site

# Sitemap
curl http://localhost/sitemap.xml
```

3. **Редиректы**
```bash
# Создать
curl -X POST http://localhost/api/seo/redirects \
  -H "Content-Type: application/json" \
  -d '{"from_url":"/old","to_url":"/new","status_code":301}'
```

---

## Статус готовности MVP

| Компонент | Статус | Примечание |
|-----------|--------|------------|
| Pages CRUD (Backend) | ✅ Ready | Сервис, контроллер, валидация |
| Pages API | ✅ Ready | Public + Admin эндпоинты |
| Page Renderer | ✅ Ready | 7 типов блоков |
| SEO (Sitemap, Robots) | ✅ Ready | Динамическая генерация |
| Media Upload | ✅ Ready | С SVG-санитизацией |
| Auth (Admin) | ✅ Ready | Login, RBAC, сессии |
| Settings API | ✅ Ready | С поддержкой меню |
| Redirects API | ✅ Ready | Валидация, CRUD |
| Builder API | ✅ Ready | Превью-рендеринг |
| Frontend (Public) | ✅ Ready | Catch-all роутинг |

**MVP может быть запущен при наличии:**
- PHP 8.2+ и Composer (для `composer install`)
- MySQL/MariaDB базы данных
- Nginx/Apache с rewrite rules
- Node.js/npm (опционально, для frontend assets)

---

## Следующие шаги (P1 Priority)

1. User/Role CRUD UI — ✅ Уже реализовано
2. Navigation menu builder — ✅ Уже реализовано
3. Settings UI — ✅ Уже реализовано
4. Page Builder интерфейс (WYSIWYG) — В процессе (UI готов, нужен Vue компонент)
5. API Документация (Swagger/OpenAPI) — Не реализовано
6. Тесты (PHPUnit) — Стенд, нужны тесты
7. CI/CD pipeline — Не реализовано

---

## Примечания

- Все новые файлы следуют PSR-4 autoloading стандарту
- Используются type hints и return types (PHP 8.2)
- Все запросы к БД используют Eloquent ORM
- Миграции совместимы с MySQL/MariaDB
- Frontend использует Blade + Alpine.js/Vue (Inertia-ready)
- Поддержка softDeletes для восстановления удалённых страниц
- Activity logging для аудита всех действий
