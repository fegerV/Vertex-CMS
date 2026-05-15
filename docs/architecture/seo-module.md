# SEO Module Architecture

Last updated: `2026-05-14`

## Direction

VertexCMS SEO should behave as a builtin module, not as a thin collection of form fields.

The target shape is:

- metadata first
- runtime aware
- audit driven
- contract based
- extensible without rewriting the public renderer

## Current builtin layers

### Metadata layer

Implemented:

- `SeoMeta` model for pages
- `seo_json` for term archives
- title, description, canonical, robots, Open Graph and schema JSON
- page and term archive edit forms

Primary code:

- `app/Seo/Services/SeoMetaService.php`
- `resources/views/admin/pages/partials/_seo.blade.php`
- `resources/views/admin/taxonomies/terms/partials/form.blade.php`

### Public rendering layer

Implemented:

- public page meta rendering
- public term archive meta rendering
- `sitemap.xml`
- `robots.txt`

Primary code:

- `app/Seo/Http/Controllers/SitemapController.php`
- `app/Seo/Http/Controllers/RobotsController.php`
- `resources/views/frontend/page.blade.php`
- `resources/views/frontend/term-archive.blade.php`

### Redirect runtime

Implemented:

- redirect storage in `redirects`
- public redirect resolver middleware before catch-all page routing
- hit counter increment on resolved redirects

Primary code:

- `app/Seo/Services/RedirectResolver.php`
- `app/Seo/Http/Middleware/ResolveSeoRedirect.php`
- `app/Seo/Http/Controllers/RedirectController.php`

## Audit layer

Implemented:

- server-side audit aggregation for published pages and term archives
- coverage metrics for explicit SEO titles and descriptions
- sitemap and robots conflict detection
- duplicate title detection
- redirect runtime status and top-hit redirects

Primary code:

- `app/Seo/Services/SeoAuditService.php`
- `app/Seo/Services/SeoContentAnalysisService.php`
- `app/Seo/Http/Controllers/SeoDashboardController.php`
- `resources/views/admin/seo/dashboard.blade.php`

Implemented content signals:

- H1 presence and multiple-H1 detection for published pages
- image alt coverage for builder image and gallery blocks
- meta description hints based on extracted builder text
- redirect runtime visibility with dedicated admin manager UI

## Architectural rules

### 1. Runtime beats stored intent

The module should report what the public site will actually expose, not only what editors typed into fields.

Examples:

- sitemap inclusion depends on both `include_in_sitemap` and `robots`
- empty SEO title may still render through fallback logic, but should still be audited as incomplete authoring
- redirects are not considered “implemented” until public middleware resolves them

### 2. Contracts over widget logic

The admin UI must remain a thin host over reusable services.

That means:

- controllers gather data
- services perform audit and runtime decisions
- Blade displays output

### 3. Separate authoring from analysis

Editing metadata and analysing metadata are distinct concerns.

- `SeoMetaService` persists author input
- `SeoAuditService` evaluates quality and conflicts

### 4. Extend by slice

New SEO features should land as independent slices:

- sitemap enhancements
- redirect patterns
- content analysis
- schema presets
- search console integrations

Each slice should add:

- a backend service
- validation
- tests
- admin visibility when relevant

## Recommended next slices

- bulk redirect tools: import/export, collision detection, loop prevention
- deeper content scoring: keyword focus, internal links, title/description snippet quality
- sitemap segmentation for news, media and taxonomies
- search console / analytics connectors
- schema preset registry instead of raw JSON-only authoring
