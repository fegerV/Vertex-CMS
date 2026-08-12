# Code audit — 2026-08-12

## Scope

The audit covered PHP syntax, the Laravel feature suite, the Vite production build, route registration, unfinished-code markers, model factories, security middleware registration, and builder block rendering.

## Fixed in this pass

- Restored the missing `User::factory()` integration and added the project factory namespace to Composer's development autoloader.
- Registered the implemented security middleware (`SecureHeaders`, `SessionGuard`, and `BasicRateLimiter`) in the global Laravel middleware stack.
- Removed unauthenticated duplicate system/cache API routes.
- Protected the AI provider and draft endpoints with Sanctum.
- Resolved the `/ai/chat` naming collision by moving the unrelated legacy endpoint to `/ai/legacy-chat`.
- Replaced an inline Blade conditional in an HTML attribute that compiled into invalid PHP.

## Confirmed remaining defects

### P0 — tests and contracts

1. `Tests\\Feature\\ApiAdminAccessControlTest` still targets the old `/api/*` contract while the current admin API is also registered under `/admin/api/*`. The route registration strategy in `App\\Core\\Support\\RouteRegistrar` duplicates all of `routes/api.php`; it should be split into dedicated public, authenticated API, and session-admin route files.
2. Backup and IP-filter tests pass a legacy `role` column to the user factory, but the current schema uses the `roles` many-to-many relation. Tests should attach seeded roles instead of writing a removed column.
3. E-commerce tests import `App\\Modules\\Ecommerce\\Models\\Product`, while the implementation is under `App\\Ecommerce`. These tests also assume factories that do not exist.
4. `InstallerAccessTest` is only a placeholder and does not verify installer access or lockout behavior.
5. Several builder rendering tests assert obsolete Tailwind/`vc-*` markup while the renderer now emits `pb-*` design-system classes. Either compatibility classes must be intentional, or tests must be migrated to the current renderer contract.

### P1 — route and API architecture

1. `routes/api.php` mixes admin CRUD, media session endpoints, AI drafts, analytics, form-module inclusion, and legacy AI services. This makes route prefixing and middleware inheritance difficult to reason about.
2. The same route file is loaded by Laravel as `/api` and by `RouteRegistrar` as `/admin/api`, which previously caused duplicate names and controller collisions. Split files rather than loading a single file twice.
3. Media routes use `web` and session authentication inside the API route file; move them to the admin route tree and apply explicit permissions.

### P1 — incomplete integrations

1. AI draft generation is an internal deterministic draft engine; external provider execution remains unimplemented.
2. The e-commerce namespace and test contract are inconsistent and the module is not yet test-runnable.
3. Builder block templates and their tests have drifted between legacy Tailwind markup and the current design-system markup.

### P2 — maintainability

1. Remove tracked compiled Blade views and generated build artifacts from version control, then make CI build them. Their presence masks missing-manifest failures and creates noisy diffs.
2. Remove production `console.log` calls from the page-builder and forms scripts or gate them behind a development flag.
3. Standardize `App\\AI` versus `App\\Services\\AI` naming. The case-sensitive split is easy to break on Linux and obscures ownership.
4. Break up `routes/api.php` and large builder Vue components into bounded modules with route-level and component-level contract tests.

## Verification baseline

- Vite production compilation succeeds.
- AI module and security-core architecture focused tests pass after the fixes.
- The full suite is not green: failures remain in legacy API access tests, backup/IP-filter role setup, e-commerce namespace/factories, and outdated block-rendering assertions.
