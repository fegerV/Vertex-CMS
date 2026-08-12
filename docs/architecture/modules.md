# Public module architecture (v1)

## Compatibility boundary

VertexCMS 1.x treats the interfaces in `App\Contracts`, the documented events, the `module.json` manifest, and `/api/v1` response shapes as public compatibility boundaries. Patch and minor releases may add optional methods only through a new interface; they do not remove or change existing methods. Implementations under `App\*\Services` are internal and modules should resolve contracts from the container.

## Manifest

A module lives below a configured `modules.scan_paths` directory and provides `module.json`. It declares a unique `id`, semantic `version`, `tier`, activation policy, providers, dependencies, permissions, routes and assets. Core constraints are checked before activation. Providers own route and migration loading; modules must not edit core routes or migrations.

## Lifecycle contract

A programmatic module may implement `App\Contracts\ModuleContract`:

1. `id()` returns the immutable manifest id.
2. `register()` binds services without querying the database or depending on routes.
3. `boot()` registers runtime integrations after all services exist.

Use `SettingsRepositoryContract` rather than the settings model. Use `CacheInvalidatorContract` with the stable domains `settings`, `pages`, `menus`, `seo`, or `all`. Invalidating `all` should be reserved for operational tools.

## Events and hooks

`SettingsImported` is emitted after a transactional import and exposes imported keys plus schema version. Backup integrations implement `BackupHookContract` and register an instance with `BackupHookRegistry`. Hooks receive `database` or `files`, and run before, after, or after a failure. A hook may stop a backup in `beforeBackup`; failure hooks must not expose credentials to users.

## Settings portability

Resolve `SettingsTransferContract`. Export documents use format `vertexcms-settings` and schema `1.0`. Secrets are excluded unless an authenticated, explicit trusted export opts in. Import ignores unknown settings for forward compatibility, rejects unknown document schemas, and does not import secrets unless explicitly allowed.

## Module checklist

- namespace all classes and avoid core file modifications;
- depend on contracts and documented events, not concrete services;
- include reversible, additive migrations;
- declare permissions and protect admin routes;
- preserve `/api/v1` types and error envelopes;
- invalidate the narrowest cache domain after writes;
- add install, upgrade, authorization and API contract tests.
