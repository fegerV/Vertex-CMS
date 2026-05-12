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
- Analytics foundation: public traffic aggregation, admin analytics dashboard, top pages and term archive traffic overview.
- Admin layout foundation: общий Blade layout, sidebar, topbar, единый flash output.
- RBAC foundation: route-level permission middleware, role permission mapping, permission-aware navigation.
- Users/Roles foundation: CRUD пользователей, назначение ролей, просмотр и редактирование permissions ролей.
- Settings foundation: UI и persistence для site/seo/api/ai/pwa/cache, public settings API, PWA manifest, AI sidebar scaffold.
- Builder advanced foundation: 60+ block definitions, revisions, preview, export/import sections, template apply.
- Custom fields foundation: `custom_fields_json`, field groups, reusable presets, apply/save/update/delete preset workflow, template/scope rules.
- Documentation foundation: roadmap, versioning, API strategy, AI, PWA/theme/taxonomy, installer.

Automated verification на 2026-05-12:

- `phpunit.xml` проходит в локальном portable PHP runtime: `35 tests`, `236 assertions`.
- Покрыты P0 public page flow, P2 builder contract, P3 public/auth API v1, P4 AI module и P5 taxonomy/PWA contract-сценарии.
- Manual QA checklist вынесен отдельно в `docs/manual-qa.md`.

Не проверено end-to-end:

- Нужен прогон сценариев в браузере и на реальном web runtime.
- Миграции и сиды нужно отдельно прогнать на целевой БД/окружении.
- Интеграции с внешними AI provider SDK ещё не подтверждены живыми ключами.

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

Базовый объём MVP:

- Builder schema validation.
- Блоки Heading, Text, Button, Image, Divider, FAQ, HTML.
- Canvas страницы.
- Панель настроек выбранного блока.
- Save draft.
- Publish.
- Preview.
- Renderer partials.

Acceptance criteria базового MVP:

- Страница сохраняется как JSON.
- Неизвестный block type не ломает страницу.
- HTML-блок проходит sanitization.
- Блок можно удалить и после сохранения он исчезает.

Статус: `Базовый MVP полностью реализован в коде. Advanced builder значительно расширен сверх исходного объёма, но runtime/manual QA и end-to-end подтверждение ещё не завершены`.

Что закрыто на уровне кода:

- JSON-сохранение структуры страницы.
- Schema validation и нормализация builder content.
- Публичный renderer с fallback-поведением для неизвестных block types.
- Sanitization HTML-блоков через backend pipeline.
- Удаление блоков и сохранение результата без возвращения удалённого контента.
- Preview перед публикацией.
- Basic builder и advanced builder для редактирования одной и той же JSON-структуры.

Что уже есть сверх базового MVP:

- Basic builder и advanced builder.
- Preview.
- Revisions и autosave.
- Export/import sections.
- Template apply workflow.
- Drag-and-drop reorder для секций и блоков.
- Inline block actions для перемещения, дублирования и удаления.
- Visual insertion cues между блоками и section-local quick add palette.
- Multi-select блоков с batch duplicate/delete внутри секции.
- Keyboard shortcuts для основных builder-операций.
- Action-aware undo/redo history с coalescing для настроек блока и секции.
- Context menu и command palette для частых builder-команд.
- Обновлённый light/dark UI для canvas и внутренних control-панелей настроек блоков и секций.
- Sticky inspector state, локальные block presets/snippets и встроенный media picker прямо из builder.
- Media picker внутри repeater-полей и quick-add library для blocks, presets и visual templates/snippets.
- Shared preset library на backend settings-repository, visual preview thumbnails и nested drag-and-drop для repeater items.
- Ownership/visibility rules для builder libraries и shared template library с backend CRUD вместо только встроенных snippets.
- Встроенный Builder Library Manager для управления preset/template metadata без prompt-flow и без выхода из advanced builder.

Что ещё мешает считать модуль production-ready:

- Нет завершённого runtime/manual QA сценариев редактирования, публикации и публичного рендера.
- Нет подтверждённого end-to-end прогона на живом PHP runtime в текущем окружении.
- Contract-тесты builder save/preview/render уже есть, но drag-and-drop, command palette и library UX всё ещё требуют manual QA.

Оценка:

- Исходные acceptance criteria закрыты на уровне кода.
- По объёму функций модуль уже заметно сильнее первоначального MVP.
- Корректнее считать его `advanced builder foundation`, а не просто `MVP`.
- До полного продуктового закрытия остаётся runtime/manual QA и подтверждение end-to-end сценариев редактирования, сохранения, публикации и публичного рендера.

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

Статус: `Public API v1 contract существенно продвинут в коде: public endpoints, bearer-oriented auth surface, /api/v1/me, единый response envelope и базовый OpenAPI schema draft уже собраны. Фактическая установка Sanctum package в это окружение и runtime QA ещё не завершены`.

Что закрыто на уровне кода:

- `/api/v1/public/pages`.
- `/api/v1/public/pages/by-uri` через query- и path-вариант.
- `/api/v1/public/menus/{location}`.
- `/api/v1/public/settings/site`.
- `/api/v1/auth/login`, `/api/v1/auth/logout`, `/api/v1/me` как bearer-oriented auth surface.
- `/api/v1/auth/tokens` и revoke текущих/старых токенов пользователем.
- API version в `meta.api_version`.
- Единый JSON envelope для успешных ответов и ошибок.
- Rate limiting для public, authenticated и login endpoints.
- `PageResource`, `MenuResource`, `MenuItemResource`, `PublicSiteSettingsResource`, `AuthUserResource`.
- Sanctum-ready user model, guard config, `personal_access_tokens` migration и `auth:sanctum` routing.
- Token abilities/scopes и self-service token management endpoints.
- Базовый OpenAPI schema draft в `docs/api/openapi-v1.yaml`.

Что ещё не закрыто:

- Пакет `laravel/sanctum` добавлен в `composer.json`, но не установлен в текущем окружении, потому что здесь нет `composer`.
- `composer.lock` не обновлён автоматически в этой среде.
- Public/auth API contract покрыт automated tests, но bearer flow всё ещё нужно отдельно прогнать на живом runtime после полного Sanctum bootstrap.

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

Статус: `основной P4 flow реализован в коде и покрыт automated tests: encrypted AI settings, provider registry, permission-aware AI panel, draft-only generation endpoints и AI activity logs уже есть. Реальная интеграция с внешними LLM provider SDK и runtime QA ещё не завершены`.

Что закрыто на уровне кода:

- AI settings catalog с encrypted provider keys, включая `ai.custom_api_key`.
- Restriction layer для AI secret key editing через permission `ai.manage_keys`.
- Provider registry endpoint `GET /admin/api/ai/providers`.
- Draft-generation endpoint `POST /admin/api/ai/chat` для `text`, `faq`, `cta`, `seo` и `builder`.
- Permission-aware AI panel на create/edit page screens для пользователей с `ai.use`.
- Apply-only UX: AI обновляет только поля формы, но не сохраняет страницу.
- AI activity logs без вывода secret values в metadata.
- Settings activity log больше не детализирует AI secret keys.

Что ещё не закрыто:

- Нет живой интеграции с external provider SDK (`openai-php/client`, Anthropic SDK, Ollama и т.д.).
- Генерация пока остаётся internal draft engine, а не runtime-вызовом внешней модели.
- Нет отдельного admin UI для AI-specific log review; пока используются общие system logs.
- Automated coverage добавлено для AI settings security, provider listing, draft-only generation и stable error contract.
- Остаётся runtime/manual QA UI-панели и живая проверка с внешним provider SDK.

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

Статус: `основной P5 vertical slice реализован в коде: manifest, service worker, offline route, theme metadata/fallback и taxonomy layer с page-term attachment, term archives, public taxonomy API, admin CRUD для taxonomies/terms, term archive SEO/meta и sitemap inclusion уже собраны. Runtime QA и миграции ещё не завершены`.

Что закрыто на уровне кода:

- `manifest.webmanifest` из site/PWA settings.
- `service-worker.js` и `/offline` как базовый PWA shell.
- Регистрация service worker на публичной странице при включённом PWA.
- Theme metadata через `themes/default/theme.json`.
- Theme fallback order для page views, offline views, term archives и block overrides.
- Default theme block override layer для `heading`, `text`, `button`, `divider`, `faq`, `html`.
- Базовая taxonomy model: `taxonomies`, `terms`, `termables`.
- Seeder для `category` и `tag` taxonomy с первичными terms.
- Привязка term к странице прямо из page edit/create form.
- Public term archive route `/taxonomy/{taxonomy}/{term}`.
- Public taxonomy API, включая страницы по term.
- Admin CRUD для taxonomies и terms: отдельные index/create/edit flow, nested term management, archive/meta fields и permission-aware вход из админки.
- SEO/meta для term archives: title, description, canonical URL, robots и include-in-sitemap flags.
- term-specific sitemap entries для индексируемых archive pages.

Что ещё не закрыто:

- Нет term archive OG/schema editor и более глубоких sitemap signals вроде priority/changefreq.
- Есть automated tests для manifest/offline/taxonomy API contract.
- Остаётся runtime/manual QA service worker install/offline behavior, theme fallback и term archive UX.

## Ближайший порядок реализации

1. Довести `robots.txt` до data-driven варианта.
2. Подтвердить P0 end-to-end после появления рабочего PHP runtime.
3. Перейти к visual block templating library с сохранением пользовательских block templates в БД.
