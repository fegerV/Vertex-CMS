# VertexCMS

VertexCMS MVP v0.1 is a Laravel-oriented CMS skeleton for building pages with a JSON page builder, built-in SEO, media management, and a browser installer.

## Current state

This repository now contains:

- a Laravel-style project structure;
- VertexCMS module folders under `app/`;
- route files for public, admin, API, and installer flows;
- starter migrations, models, controllers, middleware, and services;
- placeholder admin/frontend/installer views;
- baseline frontend tooling files for Vite, Vue 3, and Tailwind-ready assets.

## Local bootstrap

The current machine does not have `php` or `composer`, so the app could not be fully installed here. To finish bootstrap on a PHP-enabled machine:

1. Install PHP 8.2+ and Composer.
2. Run `composer install`.
3. Run `npm install`.
4. Copy `.env.example` to `.env`.
5. Generate an app key with `php artisan key:generate`.
6. Configure the database.
7. Run `php artisan migrate --seed`.
8. Run `php artisan storage:link`.
9. Start Vite with `npm run dev`.
10. Start the app with `php artisan serve`.

## Next recommended slice

1. Replace placeholder controllers with full Inertia responses.
2. Wire RBAC policies and middleware into route groups.
3. Implement the installer persistence flow and `.env` writer.
4. Build the first admin layout and auth screens.
5. Add request classes and form validation for all admin modules.

## Documentation

- [Documentation index](docs/README.md)
- [Roadmap](docs/roadmap.md)
- [Versioning](docs/versioning.md)
- [Installer Architecture](docs/architecture/installer.md)
- [API Strategy](docs/architecture/api-strategy.md)
- [AI Module](docs/architecture/ai-module.md)
- [PWA, Theme, Taxonomy](docs/architecture/pwa-theme-taxonomy.md)
