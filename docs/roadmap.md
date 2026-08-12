# VertexCMS Roadmap

## Видение

VertexCMS должна стать современной CMS с открытым ядром, быстрой установкой на обычный PHP-хостинг, встроенным page builder, SEO, media, API, PWA и AI-помощником для создания контента. Главная идея продукта - закрыть типовые задачи сайта без обязательной установки множества сторонних плагинов.

## Продуктовые принципы

Эти принципы определяют, чем VertexCMS должна отличаться от WordPress-подхода и похожих систем:

1. Всё работает вместе, а не рядом.
   Единая архитектура модулей на Laravel, общие contracts, согласованные настройки, кэш и ассеты вместо набора независимых плагинов.
2. Производительность является частью архитектуры.
   Быстрый публичный frontend, встроенный page cache, оптимизация ассетов, media-конверсии и измеримые Web Vitals должны быть базовой возможностью, а не платным набором аддонов.
3. AI и автоматизация являются базовыми инструментами редактора.
   AI должен быть провайдер-нейтральным, работать с JSON-структурой страницы и быть встроенным в рабочий процесс редактора, а не жить отдельным плагином.
4. Обновления должны быть безопасными и обратимыми.
   Система должна уметь проверять обновления, делать резервные копии, обнаруживать аномалии и откатывать неудачные изменения.
5. Privacy-first и compliance должны быть встроены в ядро.
   Базовая аналитика, аудит конфиденциальности, безопасная аутентификация и защита чувствительных данных должны поставляться из коробки.
6. CMS должна быть готова к headless, Git-based и collaborative workflows.
   Контент, API и деплой должны быть удобны и для редакторов, и для разработчиков, включая совместное редактирование, версионирование и автоматизацию поставки.

## Текущее состояние на 2026-08-12

- `v0.1` реализован в коде как рабочий foundation slice: installer, auth, RBAC, pages CRUD, media, SEO fields, frontend renderer, sitemap/robots, system pages и activity logs уже есть.
- `Page Builder MVP` и advanced builder ушли заметно дальше исходного MVP: drag-and-drop, revisions, autosave, templates, presets, shared libraries, command palette, shortcuts, media picker и modern light/dark UI уже собраны. Client-side advanced builder теперь живет в Vite-модулях с раздельными слоями `canvas`, `history`, `inspector`, `templates` и `commands`, а shared media library/picker использует единый UI.
- `v0.2` частично закрыт на текущем Blade admin stack: современная админка с light/dark theme, permission-aware navigation, системные экраны и стабильный public/mobile API v1 contract уже реализованы; planned Inertia/Vue migration остаётся следующим UI-этапом, а не обязательным условием usable admin.
- `v0.3` реализован как draft-first AI module: encrypted settings, provider registry, AI panel в page editor, generation flow для text/FAQ/CTA/SEO/builder draft и AI activity logs уже есть. Живая интеграция с внешними LLM provider SDK ещё впереди.
- `v0.4` и `v0.5` уже имеют реальную кодовую основу: PWA manifest, service worker, offline page, theme fallback, taxonomy models, admin CRUD, public term archives, taxonomy API, term archive SEO/meta и sitemap inclusion уже собраны.
- Privacy-first analytics foundation уже начата раньше formal `v0.7`: есть cookieless traffic aggregation и admin analytics dashboard для pages и term archives.
- Webhook integrations hardened and product-connected: the admin can create, inspect, test and remove signed HTTPS webhooks; private-network targets and protected-header overrides are rejected; order, payment and product lifecycle operations enqueue deliveries after database commit.
- Automated test suite поднят в локальном portable PHP runtime и зафиксирован в зелёном состоянии на `2026-05-12`: `35 tests`, `236 assertions`. Покрыты P0, P2, P3, P4 и P5 contract-сценарии.
- Основной незакрытый слой для уже написанных модулей: runtime/manual QA и прогон миграций в живой среде. Это ограничение верификации, а не отсутствие архитектуры или UI.
- **Forms module (`vertex-forms`)** имеет существенную готовую кодовую базу (6 таблиц, 3 контроллера, 5 сервисов, 6 моделей, конфиг, маршруты, `FieldTypeRegistry` с 15 типами полей). Админский drag & drop конструктор и REST API уже работают. Открыты задачи: интеграция с Page Builder блоком (`<x-builder.form>`), условная логика на фронте (`FormRenderer.vue`/`ConditionalLogicModal.vue`), реCAPTCHA v3 score-верификация, Turnstile, автоматическая версионирование, CSV-экспорт с пагинацией и очистка аналитики по расписанию. Форма вынесена из `Backlog после v1.0` в отдельный трек разработки.
- **Theme System (`v0.4`)** полностью реализован: глобальные CSS переменные (цвета, шрифты, типографика, отступы, радиусы, тени, breakpoints, z-index), JSON конфигурация дизайн-токенов, light/dark схемы, утилитарные классы. Файлы: `resources/css/theme.css`, `themes/default/theme.json`, `THEME_GUIDE.md`.
- **Security optional modules (`v0.7`)** реализованы как рабочие сервисы: WAF обнаруживает path traversal, script/SQL injection, запрещённые методы и сканеры; GeoIP поддерживает локальную CIDR-базу IPv4/IPv6 и country policy; HIBP проверяет пароли через k-anonymity range API; Cloudflare предоставляет zone/cache API и принимает visitor IP headers только от явно доверенных proxy ranges. Все четыре модуля остаются выключенными по умолчанию и активируются настройками окружения.
- **Post-v1 platform modules** получили исполняемый foundation slice. Marketplace и Themes Marketplace читают раздельные HTTPS-каталоги и поддерживают Ed25519-проверку пакетов; localization разрешает fallback-переводы и locale-aware URI; n8n получает подписанные идемпотентные события; visual automation выполняет условные шаги с ограничением размера workflow; recommendation engine ранжирует контент по интересам и исключает просмотренное; enterprise compliance audit проверяет privacy/export/deletion/consent/retention controls. Эти сервисы дополняют уже существующие E-commerce, Webhooks, Telegram, email queue/campaign delivery и Visual AI builder pipeline.

## Версия v0.1 - MVP Foundation

Цель: получить первую рабочую CMS, которую можно установить, открыть в браузере, войти в админку, создать страницу, собрать её из блоков и опубликовать.

Must Have:

- Web installer.
- Auth для админки.
- Dashboard.
- Users, roles, permissions.
- Settings repository.
- Pages CRUD.
- Page Builder MVP.
- Frontend renderer.
- SEO fields, sitemap.xml, robots.txt.
- Media upload.
- Basic cache clear.
- Activity logs.
- System info.

Acceptance criteria:

- CMS устанавливается через `/install`.
- После установки создаётся первый Super Admin.
- Пользователь входит в `/admin`.
- Можно создать, сохранить и опубликовать страницу.
- Публичная страница рендерится из `content_json`.
- Sitemap содержит опубликованные индексируемые страницы.
- Robots.txt открывается.
- Изображение можно загрузить и использовать в builder.
- Пользователь без прав не может выполнять запрещённые действия.

## Версия v0.2 - Stable Admin + Mobile API

Цель: довести админку до ежедневного использования и заложить API-контракт для мобильных приложений.

Функции:

- Полноценный Inertia + Vue admin layout.
- CRUD пользователей и ролей.
- CRUD настроек.
- Современный Blade admin layout с light/dark theme, пока Inertia/Vue migration не завершена.
- Улучшенный Pages CRUD.
- API v1 с token-based auth.
- API resources и pagination.
- OpenAPI-документация.
- API rate limits.
- Mobile-friendly endpoints для страниц, меню, медиа и настроек.
- Персональные access tokens для пользователей.
- Application tokens для внешних клиентов.

Acceptance criteria:

- Мобильное приложение может получить публичные страницы через `/api/v1`.
- Авторизованный мобильный клиент может получить профиль пользователя.
- API возвращает стабильные JSON-ответы с версией и ошибками в едином формате.
- Изменение внутренней структуры БД не ломает API без новой версии.

## Версия v0.3 - AI Assistant

Цель: добавить встроенную поддержку нейросетей для создания и улучшения контента.

Функции:

- Раздел настроек AI-провайдеров.
- Безопасное хранение API keys в зашифрованном виде.
- Provider registry.
- AI chat panel на странице создания и редактирования страницы.
- Генерация текста для Heading, Text, FAQ, CTA.
- Предложения SEO title и description.
- Предложения структуры страницы.
- Переписывание текста в выбранном стиле.
- Генерация alt для изображений.
- Activity logs для AI-действий.
- Ограничения по ролям и расходам.

Acceptance criteria:

- Super Admin может добавить API key провайдера.
- Editor может открыть чат на странице создания страницы, если имеет permission.
- AI может предложить текст, SEO-описание и структуру блоков.
- AI не сохраняет изменения без явного подтверждения пользователя.
- Все AI-действия логируются.

## Версия v0.4 - PWA + Theme System

Цель: сделать публичные сайты адаптивными, быстрыми и пригодными для установки как PWA.

Функции:

- Manifest generator.
- Service worker.
- Offline fallback.
- Иконки PWA.
- Настройки theme color и background color.
- Responsive theme tokens.
- Поддержка starter templates.
- Улучшенный renderer с lazy loading.
- Настройки cache strategy.

Acceptance criteria:

- Сайт проходит базовые PWA-проверки.
- Страницы корректно работают на мобильных, планшетах и desktop.
- Manifest генерируется из настроек сайта.
- Offline fallback открывается без сети после установки service worker.

## Версия v0.5 - Content Model + Taxonomy

Цель: выйти за пределы простых страниц и подготовить CMS к блогам, каталогам, базам знаний и корпоративным разделам.

Функции:

- Taxonomies.
- Terms.
- Admin CRUD для taxonomies и terms.
- Публичные term archives.
- Связь terms с pages и будущими content types.
- Фильтрация по категориям и тегам.
- Taxonomy API.
- SEO для архивов таксономий.
- Sitemap entries для архивов.

Acceptance criteria:

- Можно создать taxonomy `category` и `tag`.
- Можно привязать terms к странице.
- API возвращает страницы по term.
- Архив категории имеет SEO meta и публичный URL.

## Версия v0.6 - Performance by Default

Цель: превратить производительность из "опции" в системный стандарт продукта.

Функции:

- Data-driven page cache lifecycle с автоматической инвалидацией.
- Подготовка к edge-cache/CDN-friendly rendering.
- Фоновая конвертация медиа в WebP/AVIF.
- Lazy loading блоков и медиа.
- Улучшение renderer для снижения TTFB и CLS.
- Базовый Web Vitals Monitor в админке.
- Performance-настройки по умолчанию для shared hosting.

Acceptance criteria:

- Публикация или обновление страницы автоматически инвалидирует связанный кэш.
- Медиа могут храниться в оптимизированных форматах без ручной обработки редактором.
- Администратор видит базовые показатели LCP/CLS/INP в системном интерфейсе.
- Типовой сайт на shared hosting сохраняет предсказуемую производительность без внешних performance-плагинов.

## Версия v0.7 - Privacy, Security + Compliance

Цель: встроить modern security и privacy-first поведение в базовый продукт.

Функции:

- Cookieless-аналитика без внешних трекеров по умолчанию.
- Admin analytics dashboard для страниц и taxonomy archives.
- Базовый compliance-аудит для GDPR/CCPA related scenarios.
- Безопасное шифрование чувствительных ключей и настроек.
- Passkey/WebAuthn authentication для админки.
- Security dashboard с предупреждениями по конфигурации, статусами core/modules, рабочим Integrity Monitor, реактивным Alerts module и фоновым Scanner report.
- Политики хранения данных и аудит доступа.
- Security layer строится как hybrid architecture: встроенный `Security Core` в едином пространстве `Vertex\Security\` + опциональные toggle-модули (`waf`, `geoip`, `integrity`, `hibp`, `cloudflare`, `scanner`, `alerts`) без отдельного обязательного пакета.

Статус: `security vertical slice реализован в коде: core middleware, WAF, GeoIP policy, HIBP password checks, Cloudflare cache API, Integrity Monitor, Scanner и Alerts имеют рабочие сервисы и automated coverage. Перед production-включением внешних интеграций остаются настройка credentials/data sources и runtime QA на целевой инфраструктуре`.

Acceptance criteria:

- Базовая продуктовая аналитика доступна без установки внешнего JS-трекера.
- Администратор может включить passkey-аутентификацию для supported users.
- Система предупреждает о конфигурационных рисках безопасности и privacy.
- Чувствительные настройки не попадают в API-ответы, логи и UI в открытом виде.

## Версия v0.8 - Collaboration, Workflow + Headless DX

Цель: подготовить CMS к командной редактуре, интеграциям и headless-сценариям.

Функции:

- Pipeline ревью, согласования и публикации.
- Diff версий контента и комментарии к изменениям.
- Real-time collaborative editing foundation.
- Git-sync для контента и конфигурации, где это допустимо архитектурно.
- REST API hardening и подготовка GraphQL слоя.
- Автогенерация API-описания и SDK contract artifacts.
- CLI workflows для деплоя и синхронизации.

Acceptance criteria:

- Контент проходит через управляемый workflow draft -> review -> publish.
- Редактор и администратор видят различия между версиями контента.
- API остаётся стабильным и пригодным для headless-клиентов.
- Разработчик может использовать документированный workflow для Git-based поставки изменений.

## Версия v0.9 - Self-Healing Operations

Цель: снизить риск падений после обновлений и уменьшить операционную нагрузку на администраторов.

Функции:

- Health Monitor для PHP, БД, файлового хранилища, очередей и кеша.
- Safe update pipeline с pre-checks и post-checks.
- Инкрементальные backup hooks для БД и файлов.
- Автоматический rollback при аномалиях обновления.
- Auto-fix для зависшего кеша, прав доступа и типовых operational issues.
- Журнал операций поддержки и восстановления.

Acceptance criteria:

- Система может обнаружить деградацию после обновления и остановить rollout.
- Критические операции обновления сопровождаются резервной точкой восстановления.
- Администратор видит health-статус ключевых подсистем в одном месте.
- Типовые проблемы инфраструктуры можно диагностировать и частично исправить из интерфейса или CLI.

## Версия v1.0 - Production Release

Цель: зафиксировать стабильное ядро, совместимый API и публичную архитектуру модулей.

Функции:

- Стабильный installer.
- Стабильный admin.
- Стабильный API v1.
- Документированная модульная архитектура.
- Публичные contracts для модулей и интеграций.
- Экспорт/импорт настроек.
- Backup hooks.
- Security hardening.
- Test suite для критических сценариев.
- Release notes и upgrade guide.

Acceptance criteria:

- Обновление между minor-версиями проходит без ручных правок БД.
- API v1 не ломается в patch/minor-релизах.
- Ключевые модули ядра используют согласованные contracts, события и политики инвалидации кэша.
- Установщик проходит негативные сценарии.
- Документация покрывает установку, настройку, API и разработку модулей.

Статус: `стабильный compatibility foundation реализован: опубликованы core contracts, versioned settings transfer, backup hooks, API v1 compatibility policy, негативные installer tests, release notes и upgrade/module/install документация. Перед production tag остаётся полный runtime release checklist на поддерживаемых MySQL/PHP окружениях`.

## Backlog после v1.0

Foundation slice реализован для всех ранее перечисленных направлений. Следующий этап — productization и эксплуатационная проверка:

- Marketplace / Themes Marketplace: installation transactions, rollback, лицензирование, moderation UI и production catalog.
- Мультиязычность: persistence переводов сущностей, редактор переводов, hreflang и локализованные sitemap.
- E-commerce: payment-provider adapters, налоги, доставка, возвраты и полный checkout E2E.
- Webhooks / n8n / Telegram: production credentials, delivery observability, dead-letter/replay UI и integration E2E.
- Рассылки: subscriber persistence, unsubscribe/bounce handling, segmentation и campaign UI.
- Visual AI page generation / automation: provider-backed generation UI, workflow persistence, approvals и execution history.
- Recommendation/personalization: privacy-safe event storage, configurable strategies и online evaluation.
- Enterprise compliance: evidence storage, DSAR workflow, retention jobs, immutable exports и jurisdiction-specific packs.
