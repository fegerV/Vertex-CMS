# VertexCMS

VertexCMS MVP v0.1 is a Laravel-oriented CMS skeleton for building pages with a JSON page builder, built-in SEO, media management, and a browser installer.

## Current state

This repository now contains:

- a Laravel-style project structure;
- working CMS foundations for installer, auth, RBAC, pages, media, SEO, API, AI, PWA, taxonomy, analytics, and the JSON page builder;
- a tier-based module architecture foundation with builtin module manifests and registry classes;
- route files for public, admin, API, installer, and module-owned surfaces;
- migrations, models, controllers, middleware, services, and builder rendering infrastructure;
- frontend tooling for Vite, Vue 3, Tailwind, and a registry-driven Vue page builder with a dedicated Design Library workspace.

## Local bootstrap

This workspace includes a local PHP runtime at `tools/php/php.exe`. Use it when `php` is not available in `PATH`:

```powershell
& "E:\Project\Vertex-CMS\tools\php\php.exe" artisan test
& "E:\Project\Vertex-CMS\tools\php\php.exe" artisan view:cache
```

To finish bootstrap on a PHP-enabled machine:

1. Install PHP 8.2+ and Composer, or use the bundled `tools/php/php.exe` runtime for local Artisan commands.
2. Run `composer install`.
3. Run `npm install`.
4. Copy `.env.example` to `.env`.
5. Generate an app key with `php artisan key:generate`.
6. Configure the database.
7. Run `php artisan migrate --seed`.
8. Run `php artisan storage:link`.
9. Start Vite with `npm run dev`.
10. Start the app with `php artisan serve`.

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

## Documentation

- [Documentation index](docs/README.md)
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
