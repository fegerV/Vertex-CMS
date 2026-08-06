# Vertex CMS - Универсальная CMS нового поколения

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-8.1+-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/laravel-10+-red.svg)](https://laravel.com)

**Vertex CMS** — это мощная, модульная и расширяемая система управления контентом, созданная для разработчиков и маркетологов. Легкая установка как у WordPress, гибкость как у Laravel.

## 🚀 Быстрый старт

### Установка за 5 минут

1. **Загрузите файлы на хостинг** (через FTP или распакуйте архив)
2. **Откройте в браузере** `https://ваш-сайт.com/install`
3. **Следуйте мастеру установки** (проверка требований → БД → админ → готово!)

- a Laravel-style project structure;
- working CMS foundations for installer, auth, RBAC, pages, media, SEO, API, AI, PWA, taxonomy, analytics, and the JSON page builder;
- a tier-based module architecture foundation with builtin module manifests and registry classes;
- route files for public, admin, API, installer, and module-owned surfaces;
- migrations, models, controllers, middleware, services, and builder rendering infrastructure;
- frontend tooling for Vite, Vue 3, Tailwind, and a registry-driven Vue page builder with a dedicated Design Library workspace.

Всё! CMS готова к работе.

## ✨ Ключевые возможности

### 🎨 Для маркетологов и контент-менеджеров
- **Конструктор страниц** — Drag-and-drop builder без кодинга
- **SEO-инструменты** — редиректы, sitemap, Schema.org, мета-теги
- **Мультиязычность** — переводы через UI, SEO URL
- **E-commerce** — товары, корзина, заказы, платежи
- **A/B тестирование** — встроенная аналитика конверсий
- **Heatmaps** — тепловые карты кликов и скролла

### 🛠️ Для разработчиков
- **Модульная архитектура** — легко добавлять свои модули
- **GraphQL API** — готовый API для фронтенда
- **Очереди задач** — Redis, мониторинг через UI
- **Система хуков** — расширяйте ядро без изменения кода
- **PWA** — прогрессивное веб-приложение из коробки
- **Dark Mode** — темная тема для админки

### 📊 Аналитика и автоматизация
- **Дашборды** — встроенная аналитика
- **Вебхуки** — интеграция с внешними сервисами
- **Уведомления** — Telegram, Slack, Email
- **AI-инструменты** — генерация контента, чат-бот, умный поиск
- **AI Site Wizard** — мастер создания сайта за 8 шагов (структура, контент, SEO, изображения)

### 💰 SaaS и биллинг
- **Планы подписок** — управление тарифами
- **Лимиты использования** — контроль ресурсов
- **White Label** — брендирование под клиента
- **Магазин модулей** — монетизация расширений

### 🔒 Безопасность и производительность
- **GDPR Compliance** — баннер куки, аудит действий
- **IP Filter** — blacklist/whitelist
- **2FA** — двухфакторная аутентификация
- **Rate Limiting** — защита от DDoS
- **Кеширование** — Redis, многоуровневый кэш
- **CDN** — интеграция с AWS S3, Cloudflare

## Current stage

VertexCMS is no longer just an MVP skeleton. The repo is now at an `advanced foundation` stage:

- backend and rendering contracts for the page builder are implemented and tested;
- the compiled Vue builder is the primary advanced editor shell and now includes a dedicated Design Library workspace for templates, starters, and presets;
- module loading is moving from manual wiring toward manifest-driven discovery.

## Next recommended slice

1. Complete module bootstrap cleanup and provider discovery.
2. Run the full Laravel test suite again and record the refreshed status.
3. Continue Breakdance-style canvas direct-manipulation work in the advanced builder.
4. Expand frontend rendering parity so more registry blocks have first-class public renderers.
5. Continue runtime/manual QA across installer, builder, API, and module screens.

## 📦 Что включено

| Модуль | Статус | Описание |
|--------|--------|----------|
| Page Builder | ✅ | Конструктор страниц с drag-and-drop |
| Media Manager (DAM 2.0) | ✅ | Умная обработка изображений и видео |
| E-commerce | ✅ | Полный цикл: товары → заказ → оплата |
| SEO Tools | ✅ | Редиректы, sitemap, Schema.org |
| Multi-language | ✅ | Переводы через БД, переключатель |
| Analytics | ✅ | Дашборды, heatmaps, A/B тесты |
| Backup System | ✅ | Авто-бэкапы, восстановление, UI |
| Queue Monitor | ✅ | Управление очередями через UI |
| User & Roles | ✅ | RBAC, аудит, 2FA |
| Updates System | ✅ | Авто-обновления с откатом |
| Notifications | ✅ | Real-time уведомления (Reverb) |
| PWA | ✅ | Service Worker, manifest |
| GraphQL | ✅ | API для фронтенда |
| Webhooks | ✅ | Интеграции с внешними сервисами |
| AI Services | ✅ | Генерация контента, анализ, Site Wizard |
| SaaS Billing | ✅ | Подписки, лимиты |

## 🎯 Идеально подходит для

- 🏢 Корпоративных сайтов
- 🛍️ Интернет-магазинов
- 📰 Новостных порталов
- 🎓 Образовательных платформ
- 💼 SaaS-сервисов
- 🌐 Мультиязычных проектов
- 📱 Progressive Web Apps

## 🚀 Требования

- PHP >= 8.1
- MySQL >= 5.7 / PostgreSQL >= 10 / SQLite >= 3.8
- Redis (рекомендуется для очередей)
- Web-сервер: Apache/Nginx
- Минимум 512 MB RAM

## 📖 Документация

Полная документация доступна в файле [INSTALL.md](INSTALL.md)

**Разделы документации:**
- [Установка на хостинг](INSTALL.md#метод-1-веб-мастер-установки-рекомендуется)
- [CLI установка](INSTALL.md#метод-2-установка-через-cli-для-vps)
- [Конфигурация](INSTALL.md#конфигурация)
- [API документация](INSTALL.md#api-документация)
- [Решение проблем](INSTALL.md#решение-проблем)

## 🧪 Тестирование

```bash
# Запуск всех тестов
php artisan test

# Тесты с покрытием
php artisan test --coverage
```

## 🤝 Сообщество и поддержка

- 📚 **Документация:** https://docs.vertexcms.com
- 💬 **Форум:** https://forum.vertexcms.com
- 📱 **Telegram:** https://t.me/vertexcms
- 🐛 **Bug Tracker:** https://github.com/vertexcms/core/issues
- ✉️ **Email:** support@vertexcms.com

## 📄 Лицензия

Vertex CMS распространяется под лицензией **MIT**.

Для коммерческого использования (SaaS, White Label, магазин модулей) требуется **Commercial License**.

👉 Подробнее: https://vertexcms.com/pricing

---

**Vertex CMS** — создано разработчиками для разработчиков. 🚀

## 📚 Документация

- [Documentation index](docs/README.md)
- [AI Site Wizard Guide](docs/AI_SITE_WIZARD.md) - Полное руководство по мастеру создания сайта
- [Unimplemented Functions Plan](docs/unimplemented-functions-plan.md)
- [VertexCMS vs WordPress](docs/vertexcms-vs-wordpress.md)
- [Roadmap](docs/roadmap.md)
- [Versioning](docs/versioning.md)
- [Installer Architecture](docs/architecture/installer.md)
- [API Strategy](docs/architecture/api-strategy.md)
- [AI Module](docs/architecture/ai-module.md)
- [PWA, Theme, Taxonomy](docs/architecture/pwa-theme-taxonomy.md)
- [Page Builder Architecture](docs/architecture/page-builder.md)
- [Module Tiers](docs/architecture/module-tiers.md)
- [Current Status](docs/status/current-status.md)
- [Builder Prototype QA](docs/status/builder-prototype-qa.md)