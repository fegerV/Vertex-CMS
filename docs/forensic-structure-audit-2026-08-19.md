# Forensic-аудит структуры проекта VertexCMS

Дата аудита: 2026-08-19.

Цель: найти структурный хаос, возникший из-за параллельной разработки разными агентами/разработчиками с разными соглашениями, и определить канонические реализации с минимальным риском для рабочей системы.

## Методика

Проверены:

- дерево `app/`, `modules/`, `routes/`, `config/`, `database/`, `resources/`, `docker/`;
- дубли коротких имён PHP-классов и FQCN;
- `use`-imports, которые не резолвятся в текущей PSR-4 карте Composer;
- маршруты, подключенные напрямую из `routes/admin.php`, и route-файлы, лежащие рядом, но не подключенные;
- пересекающиеся сервисные слои и прямой обход repository/service abstractions;
- потенциально устаревшие директории и смешение модульной архитектуры с legacy `app/Http`.

Команды, использованные для проверки:

```bash
find . -maxdepth 2 -type f | sort
find app modules routes resources database docker -path '*/node_modules' -prune -o -type f \( -name '*.php' -o -name '*.js' -o -name '*.vue' -o -name '*.blade.php' -o -name '*.yml' -o -name '*.yaml' -o -name '*.json' \) -print
python3 <custom duplicate/import scanner>
php artisan route:list
composer dump-autoload --dry-run
```

## Executive summary

Проект в целом импортируется, но архитектура не является однородной. Самые рискованные признаки хаоса:

1. **Смешаны две архитектурные эпохи контроллеров**: новая доменная раскладка (`app/Admin`, `app/System`, `app/Content`, `app/Seo`, `app/Builder`) и legacy-слой `app/Http/Controllers/Admin/*`.
2. **В `routes/admin.php` есть реальные дубли маршрутов** для media, custom field groups, system/cache/logs/info и queue endpoints. Это не косметика: при кешировании маршрутов и генерации URL дубли имён могут дать непредсказуемый результат.
3. **Forms module имеет два service provider файла с одним FQCN `Vertex\Forms\VertexFormsServiceProvider`**. Канонический provider находится под PSR-4 path `modules/vertex-forms/src/`, а файл в корне модуля является устаревшим дубликатом.
4. **Security реализован в двух местах**: legacy/core middleware и новый namespaced security core `app/Vertex/Security`. Каноническим для optional security modules следует считать `app/Vertex/Security`, но login-security остаётся отдельным auth-slice.
5. **Есть unresolved imports**: `App\Models\Post`, `App\Models\Image`, `App\Models\Ecommerce\Product`. Эти классы отсутствуют в текущей модели проекта.
6. **Есть неподключенный route-файл `routes/admin/ai-rag.php`**, тогда как похожие SEO/AI RAG routes частично живут в `routes/admin/seo.php`.
7. **Forms admin routes подключены дважды концептуально**: host `routes/admin.php` вручную требует `modules/vertex-forms/routes/admin.php`, а legacy provider в корне модуля также пытался грузить module routes. Канонический provider в `src/` уже убрал автозагрузку routes, поэтому текущая рабочая система зависит от ручного подключения host-приложением.

## Canonical implementation matrix

| ENTITY | FILE | ROLE | USED BY | DUPLICATE OF | RECOMMENDED STATUS |
|---|---|---|---|---|---|
| Admin dashboard | `app/Admin/Http/Controllers/DashboardController.php` | Канонический admin dashboard controller для `/admin` | `routes/admin.php` через `DashboardController::class` | `app/Http/Controllers/Analytics/DashboardController.php` только по short name | KEEP |
| Analytics dashboard | `app/Http/Controllers/Analytics/DashboardController.php` | Аналитический dashboard/виджеты; не должен называться как admin dashboard в архитектурной документации | Не найден в `routes/admin.php`; проверить отдельные routes перед удалением | short-name duplicate of admin dashboard | UNKNOWN |
| Queue web controller | `app/System/Http/Controllers/QueueController.php` | HTML/admin queue screens: index, show, retry/delete/flush failed jobs | `routes/admin.php` для `system/queues*` | `app/Http/Controllers/Admin/QueueController.php` | KEEP |
| Queue API controller | `app/Http/Controllers/Admin/QueueController.php` | Legacy/API-oriented queue JSON endpoints через Redis/Artisan | `routes/admin.php` для `api/queues*` FQCN inline | `app/System/Http/Controllers/QueueController.php` by entity | MERGE |
| System routes | `routes/admin/system.php` | Канонический modular route-file для settings/system/security | Required from `routes/admin.php` | duplicate inline routes later in `routes/admin.php` | KEEP |
| Inline system routes | `routes/admin.php` | Legacy повтор `system/info`, `system/logs`, `system/cache`, `system/cache/clear` | Same file | `routes/admin/system.php` | DELETE |
| Media routes | `routes/admin.php` | Admin media CRUD and advanced media operations | Same file | duplicated first simple block vs second simple block | MERGE |
| Custom field group routes | `routes/admin.php` | Admin CRUD for custom field groups | Same file | duplicated simple block | DELETE duplicate block |
| Forms module provider canonical | `modules/vertex-forms/src/VertexFormsServiceProvider.php` | PSR-4 autoloaded provider, binds contracts/services, loads migrations/views, does **not** auto-load routes | `bootstrap/providers.php` + Composer PSR-4 `Vertex\Forms\` | `modules/vertex-forms/VertexFormsServiceProvider.php` | KEEP |
| Forms module provider root copy | `modules/vertex-forms/VertexFormsServiceProvider.php` | Stale copy with same FQCN outside Composer PSR-4; still auto-loads module routes | Not autoloaded by current Composer map | `modules/vertex-forms/src/VertexFormsServiceProvider.php` | DELETE |
| Forms admin routes | `modules/vertex-forms/routes/admin.php` | Admin form builder/submission/export/analytics routes | Required manually from `routes/admin.php` | root provider's old `loadRoutesFrom` behavior | KEEP |
| Security core provider | `app/Vertex/Security/SecurityServiceProvider.php` | Canonical optional security modules provider and middleware aliases | Referenced by security namespace and helpers | `app/Providers/SecurityServiceProvider.php` by concept | KEEP |
| Legacy security provider | `app/Providers/SecurityServiceProvider.php` | Older app-level provider; not listed in `bootstrap/providers.php` | Not currently bootstrapped | `app/Vertex/Security/SecurityServiceProvider.php` | UNKNOWN / DELETE after verification |
| SecureHeaders middleware | `app/Vertex/Security/Middleware/SecureHeaders.php` | Canonical security core middleware; computes configured headers and dashboard status | Used by security provider/helpers | `app/Http/Middleware/Security/SecureHeaders.php`, `app/Http/Middleware/SecurityHeaders.php` | KEEP |
| Legacy SecureHeaders wrapper | `app/Http/Middleware/Security/SecureHeaders.php` | Wrapper/adapter to new security middleware | Direct route usage not found | `app/Vertex/Security/Middleware/SecureHeaders.php` | MERGE / DELETE after alias audit |
| Legacy flat SecurityHeaders | `app/Http/Middleware/SecurityHeaders.php` | Older flat middleware outside security namespace | Direct route usage not found | `app/Vertex/Security/Middleware/SecureHeaders.php` | UNKNOWN / DELETE after alias audit |
| SessionGuard middleware | `app/Vertex/Security/Middleware/SessionGuard.php` | Canonical security core session guard | Security provider aliases | `app/Http/Middleware/Security/SessionGuard.php` | KEEP |
| Legacy SessionGuard wrapper | `app/Http/Middleware/Security/SessionGuard.php` | Wrapper/adapter to new session guard | Direct route usage not found | `app/Vertex/Security/Middleware/SessionGuard.php` | MERGE / DELETE after alias audit |
| IP filter middleware core | `app/Core/Http/Middleware/IpFilterMiddleware.php` | Minimal CMS-level IP allow/block middleware using `IpFilter` model | No direct references found in routes/provider scan | `app/Http/Middleware/IpFilterMiddleware.php` | UNKNOWN |
| IP filter middleware legacy | `app/Http/Middleware/IpFilterMiddleware.php` | Legacy HTTP middleware location | No direct references found in routes/provider scan | `app/Core/Http/Middleware/IpFilterMiddleware.php` | UNKNOWN |
| AI draft module | `app/AI/Services/AiDraftService.php` | Canonical v0.3 draft-first page/builder AI flow | `app/AI/Http/Controllers/AiController.php` | legacy AI content generation services by broad domain only | KEEP |
| Legacy AI content services | `app/Services/AI/*` | Older SEO/content/image/search assistant services | SEO admin controllers/views reference some functions | `app/AI/Services/*` by domain, not exact behavior | UNKNOWN / MERGE selectively |
| RAG AI services | `app/Services/Ai/*` | Supabase/vector/RAG-specific services | RAG controllers/routes if connected | `app/AI/Services/*` by domain only | KEEP if RAG stays, otherwise MOVE under `app/AI` |
| Product model | `app/Ecommerce/Models/Product.php` | Canonical product model under ecommerce bounded context | Ecommerce controllers/services | unresolved import `App\Models\Ecommerce\Product` | KEEP |
| Bad product import | `app/Services/AI/SmartSearchService.php` | Uses nonexistent namespace `App\Models\Ecommerce\Product` | This file itself | `app/Ecommerce/Models/Product.php` | FIX |
| Missing Post model refs | `app/Http/Controllers/Admin/Seo/AiUsageController.php` | SEO AI usage controller imports `Post`, but project uses `Page` for content pages | This file itself | `app/Models/Page.php` conceptually | FIX / UNKNOWN |
| Missing Image model refs | `app/Http/Controllers/Admin/Seo/AiUsageController.php` | SEO AI usage controller imports `Image`, but project uses `Media` | This file itself | `app/Models/Media.php` conceptually | FIX / UNKNOWN |
| Admin AI RAG routes | `routes/admin/ai-rag.php` | Separate RAG route file for knowledge base and chat | Not required from `routes/admin.php` | overlapping AI/SEO route concepts in `routes/admin/seo.php` | UNKNOWN / CONNECT or DELETE |
| Public API routes | `routes/public_api.php` | Public/mobile API route slice | Check bootstrap route registration before changing | `routes/api.php` by broad API domain | KEEP if registered |
| SEO redirects controller canonical | `app/Seo/Http/Controllers/RedirectController.php` | Domain controller for redirect CRUD/logs | `routes/admin/seo.php` | `app/Http/Controllers/Admin/Seo/RedirectsController.php` | KEEP |
| Legacy SEO admin controllers | `app/Http/Controllers/Admin/Seo/*` | Large older SEO feature set: AI usage, schema, social, keyword maps, search console | `routes/admin/seo.php` still references many | `app/Seo/Http/Controllers/*` partially | MERGE selectively |
| Page model | `app/Models/Page.php` | Canonical page/content model | Page controllers, builder, renderer, sitemap/API | no duplicate model file found | KEEP |
| User model | `app/Models/User.php` | Canonical Laravel user model | Auth/admin/RBAC | no duplicate model file found; `LoginUserModel` is helper/auth-slice model | KEEP |
| Login user model | `app/Security/Login/Models/LoginUserModel.php` | Login-security bounded-context representation/helper | Login-security services | `app/Models/User.php` by entity | UNKNOWN / MERGE only if it duplicates Eloquent user behavior |
| Settings repository | `app/Core/Services/SettingsService.php` + `App\Contracts\SettingsRepositoryContract` | Canonical centralized settings abstraction | Controllers/services via DI and helper | Direct `Setting::` access in older code | KEEP |
| Direct settings model access | multiple legacy controllers/services | Bypasses settings repository and cache/catalog semantics | Legacy code paths | `SettingsService` | MERGE gradually |
| Database migrations root | `database/migrations` | Canonical app migrations | Laravel migrator | module migrations if copied/published | KEEP |
| Forms module migrations | `modules/vertex-forms/database/migrations` | Canonical module-local migrations loaded by forms provider | Forms provider `loadMigrationsFrom` | published copies in root DB path if present later | KEEP |

## Detailed findings

### 1. Route-level duplication is the highest immediate risk

`routes/admin.php` first includes modular files (`pages`, `users`, `taxonomies`, `email`, `system`, `seo`) and then later repeats several routes inline. Duplicated names include:

- `admin.custom-field-groups.index/store/update/destroy`;
- `admin.media.index/store/update/destroy`;
- `admin.system.info/logs/cache/cache.clear`.

Recommendation: keep `routes/admin/*.php` as canonical for domain slices. Keep the expanded media operations inline only until they are moved into `routes/admin/media.php`; remove duplicated simple declarations before enabling `route:cache` in production.

### 2. Service provider duplication in `vertex-forms`

Two files declare the same FQCN. Composer maps `Vertex\Forms\` to `modules/vertex-forms/src/`, so the root-level provider is invisible to autoload unless manually required. It also contains old behavior that loads web/admin routes from the provider. The canonical provider in `src/` intentionally avoids route auto-loading so the host app can place routes inside its own prefix/middleware/name groups.

Recommendation: delete the root provider after confirming no external packaging process still references it. Until deletion, document it as stale and never edit both copies.

### 3. Security namespace split

Security exists in at least four places:

- `app/Vertex/Security/*` for optional modules (WAF, GeoIP, HIBP, Cloudflare, Integrity, Scanner, Alerts);
- `app/Security/Login/*` for login/two-factor/hide-admin/password-expiry;
- `app/Http/Middleware/Security/*` as wrappers/legacy copies;
- `app/Providers/SecurityServiceProvider.php` as an older provider not bootstrapped in `bootstrap/providers.php`.

Canonical decision: keep `app/Vertex/Security` for optional security modules and keep `app/Security/Login` for login-auth hardening. Do not merge login-security into optional security modules until there is a product decision, because it participates in authentication flow.

### 4. AI namespace split

There are three AI-related locations:

- `app/AI/*`: current draft-first AI module aligned with roadmap;
- `app/Services/AI/*`: older content/image/chat/search services, with some SEO UI usage;
- `app/Services/Ai/*`: RAG/vector/Supabase services.

Canonical decision: use `app/AI` for product AI module. Keep RAG services only if `routes/admin/ai-rag.php` is connected or moved under the same module. Treat `app/Services/AI` as legacy unless a controller still needs it.

### 5. Imports that cannot resolve

The static import scan found imports that do not map to files in Composer's app/module PSR-4 tree:

| FILE | BAD IMPORT | LIKELY CANONICAL TARGET | STATUS |
|---|---|---|---|
| `app/Http/Controllers/Admin/Seo/AiUsageController.php` | `App\Models\Post` | `App\Models\Page` or remove if dead code | FIX / UNKNOWN |
| `app/Http/Controllers/Admin/Seo/AiUsageController.php` | `App\Models\Image` | `App\Models\Media` or remove if dead code | FIX / UNKNOWN |
| `app/Services/AI/SmartSearchService.php` | `App\Models\Ecommerce\Product` | `App\Ecommerce\Models\Product` | FIX |

### 6. Repositories are not consistently enforced

The Forms module has a clear repository contract (`FormRepositoryInterface` -> `EloquentFormRepository`). Core CMS domains mostly use service classes, but older controllers sometimes directly touch Eloquent models or DB facades. This is not always wrong in Laravel, but it means there is no project-wide repository rule.

Recommendation: do not introduce repositories everywhere. For low-risk stabilization, enforce repositories only inside modules that already have them (`vertex-forms`) and enforce service-layer access for pages, ecommerce, media and settings where services already exist.

### 7. Static/template/storage/config path risks

No single fatal wrong path was proven by static scan, but there are risk clusters:

- module views use namespace `forms` and publish to `resources/views/vendor/forms`; host templates must consistently call `view('forms::...')`;
- forms config is `config('forms')`, while module file lives under `modules/vertex-forms/config/forms.php`; provider merge is required;
- storage/media paths are split across core media services and Laravel filesystem config; avoid hard-coded `public/uploads` paths in new code;
- Docker config exists in both root `Dockerfile`/`docker-compose.yml` and `docker/nginx`; verify paths during deployment rather than assuming Laravel Sail conventions;
- migration paths are split between root app and module-local migrations; avoid copying module migrations into root unless publishing for distribution.

## Recommended remediation order

1. **Route deduplication first**: remove duplicate admin route declarations or move media routes into `routes/admin/media.php`.
2. **Fix unresolved imports**: replace bad model namespaces or remove dead imports/code.
3. **Forms provider cleanup**: delete or explicitly mark `modules/vertex-forms/VertexFormsServiceProvider.php` as stale after package/distribution check.
4. **Queue controller merge**: combine HTML and API endpoints under `app/System/Http/Controllers/QueueController.php` or split into `QueueController` and `QueueApiController` under `app/System`.
5. **Security alias audit**: keep `app/Vertex/Security`; remove wrappers only after checking middleware aliases and route middleware usage.
6. **AI/RAG route decision**: either connect `routes/admin/ai-rag.php` from `routes/admin.php` with permissions or delete it if superseded by SEO routes.
7. **Service boundary documentation**: write a small architecture note defining when to use Service vs Repository to prevent future AI agents from creating `Manager/UseCase/Service` triples for the same entity.

## Do-not-do recommendations

- Do not mass-rename directories just to make names pretty.
- Do not merge `app/Security/Login` into `app/Vertex/Security` without auth regression tests.
- Do not delete legacy SEO controllers until `routes/admin/seo.php` is mapped method-by-method.
- Do not force repositories into every Laravel CRUD controller; use existing services where already established.
- Do not move module migrations into root migrations unless packaging/deployment requires published migrations.
