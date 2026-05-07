# План нереализованных функций

## Текущее состояние

Реализовано частично:

- Core Skeleton: структура проекта, маршруты, базовые модели, миграции, seeders.
- Installer backend: requirements, проверка БД, запись `.env`, миграции, seeders, первый администратор, `installed.lock`.
- Auth foundation: login/logout, remember me, `last_login_at`, activity logs входа/выхода.
- Pages CRUD foundation: создание, редактирование, удаление, slug/URI, revisions, activity logs.
- SEO foundation: SEO-поля страницы, robots, sitemap flag, публичные meta/OG/schema.
- Renderer foundation: базовый вывод JSON-блоков Heading, Text, Button, Divider, FAQ, HTML.
- Media foundation: upload, metadata edit, delete, SVG sanitization, activity logs.
- Documentation foundation: roadmap, versioning, API strategy, AI, PWA/theme/taxonomy, installer.

Не проверено end-to-end:

- `composer install` пока блокируется сетевой политикой PHP-процесса к Packagist.
- Laravel runtime не запускался через `artisan serve`.
- Миграции не прогонялись на реальной БД.

## Приоритет P0 - довести MVP до первого рабочего сценария

Цель: администратор устанавливает CMS, входит в админку, создаёт страницу и видит её на публичном URL.

Функции:

- Pages CRUD: создание, редактирование, удаление, статусы.
- Slug и URI generation.
- Page revisions при сохранении.
- Базовый frontend renderer для `content_json`.
- SEO fields для страницы.
- Sitemap и robots.txt на реальных данных.
- Базовая media upload.
- Cache clear.

Acceptance criteria:

- Можно создать страницу из админки.
- Slug создаётся автоматически и проходит validation.
- URI уникален.
- Опубликованная страница открывается публично.
- Черновик публично не открывается.
- После каждого сохранения создаётся revision.

## Приоритет P1 - управляемая админка

Цель: сделать админ-панель пригодной для регулярной работы.

Функции:

- Admin layout с sidebar.
- Навигация по разделам.
- CRUD пользователей.
- CRUD ролей.
- Назначение permissions.
- Settings UI.
- System info UI.
- Activity logs UI с фильтрами.

Acceptance criteria:

- Пользователь видит только доступные разделы.
- Viewer не может сохранять изменения.
- Editor не видит управление пользователями.
- Super Admin видит системные разделы.

## Приоритет P2 - Page Builder MVP

Цель: собрать страницу из JSON-блоков и отрендерить её публично.

Функции:

- Builder schema validation.
- Блоки Heading, Text, Button, Image, Divider, FAQ, HTML.
- Canvas страницы.
- Панель настроек выбранного блока.
- Save draft.
- Publish.
- Preview.
- Renderer partials.

Acceptance criteria:

- Страница сохраняется как JSON.
- Неизвестный block type не ломает страницу.
- HTML-блок проходит sanitization.
- Блок можно удалить и после сохранения он исчезает.

## Приоритет P3 - API для мобильных приложений

Цель: подготовить стабильный API v1 для мобильных клиентов и внешних интеграций.

Функции:

- `/api/v1/public/pages`.
- `/api/v1/public/pages/by-uri`.
- `/api/v1/public/menus/{location}`.
- `/api/v1/public/settings/site`.
- token auth через Sanctum.
- API resources.
- единый формат ошибок.
- OpenAPI schema.

Acceptance criteria:

- Мобильное приложение может получить опубликованную страницу по URI.
- API не отдаёт внутренние Eloquent-модели напрямую.
- Ошибки имеют стабильный формат.
- Версия API отражена в ответе.

## Приоритет P4 - AI Module

Цель: добавить AI-помощника для страниц, текста, структуры и SEO.

Функции:

- Settings для AI provider keys.
- Шифрование ключей.
- Provider registry.
- AI chat panel на создании/редактировании страницы.
- Генерация текста, FAQ, CTA.
- Генерация SEO title и description.
- Генерация builder JSON draft.
- AI activity logs.

Acceptance criteria:

- Super Admin может добавить AI key.
- Editor с `ai.use` видит AI chat.
- AI не сохраняет изменения без подтверждения.
- Secret keys не попадают в logs и responses.

## Приоритет P5 - PWA, themes, taxonomy

Цель: подготовить продукт к более широким сценариям сайтов.

Функции:

- Manifest generator.
- Service worker.
- Offline page.
- Theme metadata.
- Theme block overrides.
- Taxonomies.
- Terms.
- Term archives.
- Taxonomy API.

Acceptance criteria:

- Сайт может получить `manifest.webmanifest`.
- Theme renderer имеет fallback order.
- Term можно привязать к странице.
- API может вернуть страницы по term.

## Ближайший порядок реализации

1. Pages CRUD foundation.
2. Page revisions.
3. Public page visibility rules.
4. SEO meta для страниц.
5. Renderer MVP.
6. Media upload MVP.
7. Admin layout и sidebar.
8. Settings UI.
9. API v1 public read endpoints.
10. Builder MVP.
