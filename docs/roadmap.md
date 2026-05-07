# VertexCMS Roadmap

## Видение

VertexCMS должна стать современной CMS с открытым ядром, быстрой установкой на обычный PHP-хостинг, встроенным page builder, SEO, media, API, PWA и AI-помощником для создания контента. Главная идея продукта - закрыть типовые задачи сайта без обязательной установки множества сторонних плагинов.

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

## Версия v1.0 - Production Release

Цель: зафиксировать стабильное ядро, совместимый API и публичную архитектуру модулей.

Функции:

- Стабильный installer.
- Стабильный admin.
- Стабильный API v1.
- Документированная модульная архитектура.
- Экспорт/импорт настроек.
- Backup hooks.
- Security hardening.
- Test suite для критических сценариев.
- Release notes и upgrade guide.

Acceptance criteria:

- Обновление между minor-версиями проходит без ручных правок БД.
- API v1 не ломается в patch/minor-релизах.
- Установщик проходит негативные сценарии.
- Документация покрывает установку, настройку, API и разработку модулей.

## Backlog после v1.0

- Marketplace.
- Themes marketplace.
- Автообновления.
- Мультиязычность.
- E-commerce module.
- Forms module.
- Webhooks.
- n8n integration.
- Telegram integration.
- Рассылки.
- Visual AI page generation.
