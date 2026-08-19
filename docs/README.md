# Документация VertexCMS

Этот каталог хранит рабочую документацию проекта. Документы написаны так, чтобы ими могли пользоваться разработчики, продуктовая команда и будущие авторы модулей.

## Основные документы

- [Installation and configuration](installation.md) - требования, безопасная установка и перенос настроек.
- [Public module architecture](architecture/modules.md) - стабильные contracts, lifecycle, события и cache policy для модулей.
- [API v1](api/README.md) - endpoints и гарантия совместимости API v1.
- [Upgrade guide](upgrade-guide.md) - обновление 1.x без ручного изменения БД и процедура rollback.
- [VertexCMS 1.0.0 release notes](releases/1.0.0.md) - baseline первого стабильного релиза.
- [Forensic structure audit 2026-08-19](forensic-structure-audit-2026-08-19.md) - аудит структурного хаоса, дублей, stale-директорий и канонических реализаций.
- [Core Skeleton](architecture/core-skeleton.md) - базовый технический каркас проекта.
- [Unimplemented Functions Plan](unimplemented-functions-plan.md) - карта нереализованных функций и ближайший порядок реализации.
- [VertexCMS vs WordPress](vertexcms-vs-wordpress.md) - преимущества VertexCMS перед WordPress и честные ограничения.
- [Roadmap](roadmap.md) - этапы развития от MVP v0.1 до следующих версий.
- [Versioning](versioning.md) - правила версионирования ядра, API, модулей и миграций.
- [Installer Architecture](architecture/installer.md) - backend flow установки CMS через браузер.
- [API Strategy](architecture/api-strategy.md) - архитектура API для админки, мобильных приложений и внешних клиентов.
- [AI Module](architecture/ai-module.md) - поддержка нейросетей, ключей провайдеров и AI-чата на странице создания контента.
- [PWA, Theme, Taxonomy](architecture/pwa-theme-taxonomy.md) - адаптивные шаблоны, PWA и решение по таксономии.

## Как обновлять документы

- Любое новое крупное направление должно получить отдельный документ в `docs/architecture`.
- Решения, влияющие на совместимость, нужно отражать в `docs/versioning.md`.
- Изменение приоритетов продукта нужно отражать в `docs/roadmap.md`.
- Acceptance criteria лучше писать рядом с соответствующим модулем, чтобы ими можно было пользоваться при тестировании.
