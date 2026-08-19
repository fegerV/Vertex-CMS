# Vertex-CMS Development Guide

## Common Commands
- **Install Dependencies**: `composer install`, `npm install`
- **Run Migrations**: `php artisan migrate`
- **Seed Database**: `php artisan db:seed`
- **Run Tests**: `php artisan test`
- **Clear Cache**: `php artisan cache:clear`, `php artisan view:clear`, `php artisan route:clear`
- **Build Assets**: `npm run dev`, `npm run build`

## Coding Standards
- **PHP**: PSR-12, Laravel-specific conventions (e.g., using helper functions, Eloquent best practices).
- **JS**: ESLint, Vue.js Style Guide.
- **CSS**: Tailwind CSS utility classes.

## Project Structure
- `app/System`: Core system logic, services, and controllers.
- `resources/views/admin`: Admin panel views.
- `resources/views/frontend`: Public-facing views.
- `routes/`: Modular route files (`admin.php`, `api.php`, `web.php`, etc.).

## SEO & PWA
- Use the `SeoService` for meta tags and schema.
- PWA manifest is generated dynamically via `PwaManifestController`.

## Media
- Use the `MediaService` for handling uploads and metadata.
