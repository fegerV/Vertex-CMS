# План нереализованных функций

## Текущее состояние

Реализовано в коде:

- Core Skeleton: структура проекта, маршруты, базовые модели, миграции, seeders.
- Installer backend: requirements, проверка БД, запись `.env`, миграции, seeders, первый администратор, `installed.lock`.
- Auth foundation: login/logout, remember me, `last_login_at`, activity logs входа/выхода.
- Pages CRUD foundation: создание, редактирование, удаление, slug/URI, revisions, activity logs.
- SEO foundation: SEO-поля страницы, robots, sitemap flag, публичные meta/OG/schema.
- Renderer foundation: вывод JSON-блоков Heading, Text, Button, Divider, FAQ, HTML.
- Media foundation: upload, metadata edit, delete, SVG sanitization, activity logs.
- System foundation: system info, activity log filters, cache status and manual cache clear.
- Admin layout foundation: общий Blade layout, sidebar, topbar, единый flash output.
- RBAC foundation: route-level permission middleware, role permission mapping, permission-aware navigation.
- Users/Roles foundation: CRUD пользователей, назначение ролей, просмотр и редактирование permissions ролей.
- Settings foundation: UI и persistence для site/seo/api/ai/pwa/cache, public settings API, PWA manifest, AI sidebar scaffold.
- Builder advanced foundation: 60+ block definitions, revisions, preview, export/import sections, template apply.
- Custom fields foundation: `custom_fields_json`, field groups, reusable presets, apply/save/update/delete preset workflow, template/scope rules.
- Documentation foundation: roadmap, versioning, API strategy, AI, PWA/theme/taxonomy, installer.

Не проверено end-to-end:

- `composer install` пока блокируется сетевой политикой PHP-процесса к Packagist.
- Laravel runtime не запускался через `artisan serve`.
- Миграции не прогонялись на реальной БД.
- PHP lint локально не выполнен, потому что `php` отсутствует в PATH текущего окружения.

## Статус P0

Статус: `реализовано в коде, но не подтверждено end-to-end`.

Что покрыто:

- Pages CRUD из админки.
- Slug generation и validation.
- URI generation и уникальность.
- Page revisions при сохранении.
- Frontend page rendering по `content_json`.
- SEO fields для страниц.
- Sitemap на реальных данных опубликованных страниц.
- Media upload.
- Cache clear.
- Публичная недоступность draft-страниц.

Что ещё не стоит считать полностью закрытым:

- `robots.txt` пока статический и не опирается на настройки или состояние сайта.
- Нет runtime-подтверждения сценария “создать страницу в админке -> открыть публичный URL”.

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

Оценка:

- Все acceptance criteria реализованы на уровне кода.
- End-to-end проверка пока не выполнена.
- Подзадача `robots.txt на реальных данных` остаётся частично реализованной.

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

Статус: `реализовано в коде, ожидает runtime/manual QA подтверждения`.

Что закрыто:

- Admin layout с sidebar, topbar, breadcrumb-навигацией и адаптивным мобильным drawer.
- Permission-aware навигация по разделам.
- CRUD пользователей.
- Просмотр и редактирование ролей с назначением permissions.
- Settings UI с read-only режимом для ролей без `settings.edit`.
- System info UI.
- Activity logs UI с фильтрами.
- Современный light/dark UI для админки и builder-экранов.

Оценка:

- Все acceptance criteria реализованы на уровне кода и UI.
- Route-level ограничения и sidebar gating согласованы.
- Тестовый каркас для access control добавлен, но полный runtime-прогон отложен.

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

Статус: `MVP реализован в коде и частично расширен сверх исходного объёма, но не подтверждён end-to-end`.

Что уже есть сверх базового MVP:

- Basic builder и advanced builder.
- Preview.
- Revisions и autosave.
- Export/import sections.
- Template apply workflow.
- Обновлённый light/dark UI для canvas и внутренних control-панелей настроек блоков и секций.

Оценка:

- Исходные acceptance criteria закрыты на уровне кода.
- По объёму функций модуль уже сильнее первоначального MVP.
- До полного закрытия остаётся runtime/manual QA подтверждение сценариев редактирования и публикации.

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

1. Довести `robots.txt` до data-driven варианта.
2. Подтвердить P0 end-to-end после появления рабочего PHP runtime.
3. Перейти к visual block templating library с сохранением пользовательских block templates в БД.
