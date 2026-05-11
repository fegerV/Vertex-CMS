# API Docs

- [OpenAPI v1](openapi-v1.yaml) - базовый контракт публичного API для mobile-клиентов и внешних интеграций.
- Bearer auth для `/api/v1/auth/*` и `/api/v1/me` проектируется через Laravel Sanctum.
- В текущей среде `composer` недоступен, поэтому `composer.lock` не обновлён автоматически; после установки зависимостей нужно выполнить `composer install` и миграции.
