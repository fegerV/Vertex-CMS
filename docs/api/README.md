# API Docs

- [OpenAPI v1](openapi-v1.yaml) - базовый контракт публичного API для mobile-клиентов и внешних интеграций.
- Bearer auth для `/api/v1/auth/*` и `/api/v1/me` проектируется через Laravel Sanctum.
- В текущей среде `composer` недоступен, поэтому `composer.lock` не обновлён автоматически; после установки зависимостей нужно выполнить `composer install` и миграции.

## v1 compatibility guarantee

For the VertexCMS 1.x lifecycle, existing `/api/v1` fields, types, meanings, status codes and error envelope keys are not removed or changed in patch/minor releases. New optional fields and endpoints may be added. Breaking behavior requires `/api/v2` and a documented deprecation period. Consumers should ignore unknown response fields.
