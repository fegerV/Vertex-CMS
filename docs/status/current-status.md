# Current Status

## Updated

Last updated: `2026-11-01`

## What Is Implemented

### Routing and admin API surface

- the public page catch-all is now a true fallback route, so `/admin`, `/install` and `/api` routes always win over `GET /{uri}`
- the admin JSON endpoints the UI already consumed are registered again under `/admin/api/*`:
  - `ai/providers`, `ai/chat`, `ai/edit`
  - `forms` CRUD, `forms/field-registry`, import/export
  - `media`, `media/upload`, `media/folders`
  - `dashboard/stats`
- the permission catalog now defines `system.edit` and `seo.settings`, which routes required but the seeder never created
- backup pipeline fixes: the local backup directory is created on demand, and `BackupController` imports the `Storage` facade

### Core product slices already in code

### Performance monitoring

- public pages report Core Web Vitals to `POST /api/v1/public/web-vitals`; the endpoint is unauthenticated by design (anonymous visitors), runs on the API middleware group so no CSRF token is needed for `sendBeacon`, validates metric names and value bounds, and keeps only allow-listed metadata keys
- stored URLs are stripped of query strings and fragments, and the ingest respects the `web_vitals.enabled` setting
- the tracker reports each metric once on `pagehide`/`visibilitychange` instead of on every `PerformanceObserver` callback, so a page view produces a small fixed number of rows
- `/admin/web-vitals` renders per-metric averages and rating distributions, the worst recent measurements, a daily chart and a streamed CSV export, and is linked from the «Система» section for users with `system.view`

- installer flow foundation
- admin auth and RBAC
- pages CRUD
- JSON-based page builder foundation
- media library
- SEO metadata, sitemap, robots, runtime redirects, content analysis and audit dashboard
- API v1 foundation
- AI draft module foundation
- PWA/theme/taxonomy foundation
- analytics foundation

### Module system progress

- builtin module folders exist under `modules/`
- `vertex-forms` exists as a real builtin module
- `vertex-security` now has a hybrid runtime with real optional modules:
  - `Integrity Monitor`
  - `Alerts`
  - `Scanner`
- tier-based module catalog groundwork now exists via:
  - `config/modules.php`
  - `App\Modules\Support\ModuleManifest`
  - `App\Modules\Support\ModuleManifestLoader`
  - `App\Modules\Support\ModuleCatalog`
  - `App\Modules\Services\ModuleManager`

### Builder progress

- persisted builder contract remains `content_json`
- backend block registry exists in `App\Builder\Config\BlockRegistry`
- backend builder save/preview/validation flow exists
- builder direction is explicitly JSON-first, schema-driven and registry-based, not DOM-first
- TipTap packages are already installed in `package.json`
- Vue builder runtime now uses a merged registry approach:
  - `resources/js/components/builder/registry.js`
  - `resources/js/components/builder/PageBuilder.vue`
  - `resources/js/components/builder/mountPrototype.js`
- schema-driven block editing now exists for backend-defined blocks:
  - `resources/js/components/builder/blocks/SchemaBlock.vue`
  - `resources/js/components/builder/fields/SchemaField.vue`
  - `resources/js/components/builder/fields/SchemaRepeaterField.vue`
- a product architecture pass now also exists for:
  - WordPress-style page editing
  - Forminator-style form building
  - shared registry-driven editor metadata
  - see `docs/architecture/wp-style-editor-and-form-builder.md`
- page create/edit now also has a new WordPress-style Blade shell with an embedded builder mount:
  - `resources/views/admin/pages/create.blade.php`
  - `resources/views/admin/pages/edit.blade.php`
  - `resources/views/admin/pages/partials/form.blade.php`
  - `resources/views/admin/pages/partials/wp-sidebar.blade.php`
- advanced page builder shell now mounts from compiled Vue SFCs:
  - `resources/js/admin/builder/AdvancedBuilderApp.vue`
  - `resources/js/admin/builder/components/BuilderBlockRenderer.vue`
  - `resources/js/admin/builder/components/BuilderBlockSettings.vue`
  - `resources/js/admin/builder/components/BuilderSectionSettings.vue`
- Breakdance-inspired Design Library workspace now exists as a dedicated compiled Vue surface:
  - `resources/views/admin/builder/design-library.blade.php`
  - `resources/js/admin/builder/DesignLibraryApp.vue`
  - `resources/js/admin/builder/mountDesignLibrary.js`
  - JSON workspace endpoint: `/admin/api/pages/builder/design-library`
- live preview canvas now includes Breakdance-style section controls for direct manipulation:
  - quick block insertion
  - add section
  - duplicate section
  - move section up/down
  - delete section
- advanced builder shell now uses a Breakdance-like app frame:
  - global dark app bar
  - app-level device, selection, save, preview and library controls
  - dark left/right workspace panels
  - white central page canvas on a dark work area
- UX Builder-inspired delivery planning is now documented in:
  - `docs/architecture/ux-builder-inspired-delivery-plan.md`

## Current Stage

### Product stage

VertexCMS is past the `empty skeleton` stage and now sits at an `advanced foundation` stage across the main verticals.

### Builder stage

The production builder contract is real and tested on the backend.

The Vue page builder is now a mounted admin runtime:

- registry-driven
- backend-synced through `/admin/api/builder/blocks`
- schema-driven for backend block fields
- buildable with current dependencies
- mountable from the main frontend bundle
- embedded into the page create/edit form flow
- builder route no longer depends on runtime template compilation or a route-specific CSP `unsafe-eval` exception

It still needs broader manual QA, but it is no longer just an isolated prototype.

The current product-facing builder pass is focused on:

- richer template library browsing
- more visual template metadata and previews
- tighter UX around canvas/inspector interactions

while keeping `content_json` unchanged.

### Public block renderer parity

`App\Builder\Services\PageRenderer` is now the single source of truth for public block markup:

- the `22` block types that used to be shadowed by unstyled `pb-*` Blade partials are rendered by the PHP renderer
- every registry block type now has first-class public output: heading, text, list, faq, button, image, icon, counter, progress-bar, alert, breadcrumbs, accordion, tabs, collapse, modal, tooltip, testimonials, pricing-table, product-card, product-list, news-feed, seo-meta
- `news-feed` renders published pages (it previously referenced a non-existent `App\Models\Post` and crashed)
- `seo-meta` renders nothing publicly because page SEO is emitted by the public layout
- `resources/css/app.css` ships `vc-*` styles for all of the above
- `resources/js/app.js` ships `mountFrontendBlocks()` for tabs, single-open accordions, dismissible alerts, native `<dialog>` modals and animated counters
- the HTML block sanitizer now drops `script`/`style`/`iframe`/`object`/`embed`/`noscript` containers with their contents instead of leaking script bodies as text

Theme overrides keep working: `themes/{theme}/blocks/{type}` and `themes.default.blocks.{type}` are checked before the renderer. The only bundled partials left are the ones with real presentation logic of their own: `gallery`, `cart` and `form` (module view).

The last six shadowing partials (`hero`, `video`, `divider`, `spacer`, `columns`, `container`) are gone as well:

- `video` parses YouTube and Vimeo URLs into embeds, honours `ratio`, `autoplay`, `loop`, `muted` and `controls`, supports HTML5 sources and rejects non-http(s) URLs
- `divider` honours `style`, `color`, `thickness` and `width` with sanitized CSS values
- `hero`, `columns`, `container` and `spacer` moved fully into the renderer with escaped settings and nested block rendering
- a `heading` without an explicit `level` used to raise `Undefined array key "level"` inside nested columns; the renderer now falls back to `h2`

### SEO module stage

The SEO layer is now beyond a pure metadata foundation:

- pages and term archives have editable SEO fields
- `sitemap.xml` and `robots.txt` are live
- redirects now resolve on the public runtime before the catch-all page route
- an admin SEO dashboard audits coverage, sitemap conflicts, duplicate titles and content structure
- the redirects runtime now has a dedicated admin manager UI with filters and inline updates

### Forms builder stage

`vertex-forms` now has:

- a formal field registry API
- a dedicated Vue builder runtime mounted from the main app bundle
- a Blade shell that only provides mount data and routes

The old Alpine prototype is no longer the primary implementation path.

The agreed target direction is now:

- WordPress-familiar page editing for pages
- Forminator-like dedicated builder UX for forms
- Vue 3 runtime replacing the current Alpine prototype over time

## Tests And Verification

### Previously documented green suite

As documented in project docs before this update:

- `35 tests`
- `236 assertions`
- coverage across `P0`, `P2`, `P3`, `P4`, `P5`

### New verification completed in this pass

- PHP syntax checks passed for:
  - module registry classes
  - updated `VertexServiceProvider`
  - `vertex-security` skeleton classes
- isolated module manifest loader check passed and detected:
  - `vertex-core`
  - `vertex-auth`
  - `vertex-content`
  - `vertex-forms`
  - `vertex-security`
- builder-focused Laravel tests now pass:
  - `BuilderContractTest`: `4 passed`, `29 assertions`
  - `BuilderRegistryApiTest`: `1 passed`, `339 assertions`
- forms registry API test now passes:
  - `FormFieldRegistryApiTest`: `1 passed`, `216 assertions`
- `npm run build` passes with the updated Vue/TipTap prototype
- `php artisan view:cache` passes with the new page shell and form builder Blade mount
- UTF-8 / encoding repair verification completed for:
  - `resources/views/admin/pages/partials/form.blade.php`
  - `resources/views/admin/layouts/app.blade.php`
  - `modules/vertex-forms/src/Controllers/FormController.php`
- repeated builder verification still passes after encoding fixes:
  - `BuilderRegistryApiTest`: `1 passed`, `339 assertions`
  - `BuilderContractTest`: `4 passed`, `29 assertions`
- `php artisan view:cache` passes after the encoding fixes as well
- security dashboard runtime now also includes:
  - reactive alerts
  - integrity baseline/drift status
  - background scanner report with scheduled command support
- builder runtime parity pass now also passes:
  - `npm run build`
  - `BuilderRegistryApiTest`: `1 passed`, `339 assertions`
  - `BuilderContractTest`: `4 passed`, `29 assertions`
  - `php artisan view:cache`
- Breakdance-inspired Design Library pass now also passes with bundled PHP runtime `tools/php/php.exe`:
  - `npm run build`
  - `BuilderContractTest`: `17 passed`, `101 assertions`
  - `BuilderLibraryManagerTest`: `3 passed`, `21 assertions`
  - `php artisan view:cache`
- full Laravel suite now passes through bundled PHP runtime:
  - `78 tests`
  - `1239 assertions`
- live preview section-controls pass also verified:
  - `npm run build`
  - `BuilderContractTest`: `17 passed`, `101 assertions`
  - `php artisan view:cache`
  - full suite remains `78 tests`, `1239 assertions`
- Breakdance-like shell redesign pass also verified:
  - `npm run build`
  - `BuilderContractTest`: `17 passed`, `101 assertions`
  - `php artisan view:cache`
  - full suite remains `78 tests`, `1239 assertions`
- builder registry API test was added in:
  - `tests/Feature/BuilderRegistryApiTest.php`
- form field registry API test was added in:
  - `tests/Feature/FormFieldRegistryApiTest.php`
- full suite re-verified green after the routing/API/renderer pass:
  - `147 tests`
  - `3208 assertions`
  - `node --check` on `resources/js/app.js` and `php artisan view:cache`
  - `vite build` could not run locally: the installed `node_modules` is missing the Rollup native binary

## Page Builder Block Inventory

### Backend registry currently defined

The backend block catalog in `app/Builder/Config/blocks.php` currently defines `30` top-level block types:

- `heading`
- `text`
- `list`
- `faq`
- `button`
- `image`
- `video`
- `gallery`
- `icon`
- `columns`
- `container`
- `spacer`
- `divider`
- `news-feed`
- `testimonials`
- `counter`
- `pricing-table`
- `form`
- `seo-meta`
- `accordion`
- `tabs`
- `modal`
- `tooltip`
- `product-card`
- `product-list`
- `cart`
- `alert`
- `progress-bar`
- `breadcrumbs`
- `collapse`

### Vue builder runtime currently editable

The current Vue runtime is now aligned with the backend registry for all `30` backend block types through the schema-driven editor. It also retains a small hidden experimental layer for old prototype-only block aliases.

Backend-aligned blocks now editable in the mounted runtime:

- `heading`
- `text`
- `list`
- `faq`
- `button`
- `image`
- `video`
- `gallery`
- `icon`
- `columns`
- `container`
- `spacer`
- `divider`
- `news-feed`
- `testimonials`
- `counter`
- `pricing-table`
- `form`
- `seo-meta`
- `accordion`
- `tabs`
- `modal`
- `tooltip`
- `product-card`
- `product-list`
- `cart`
- `alert`
- `progress-bar`
- `breadcrumbs`
- `collapse`

### Gap between backend and Vue prototype

The previous backend/frontend gap for the main builder inventory is now closed at the editing-contract level.

Still non-aligned at the product level:

- `hero`
- `cta`
- `paragraph`
- `form-embed`

These remain experimental and are not backend-registry-first block types.

### Remaining verification still needed

- browser/manual QA of the mounted Design Library workspace and template apply flow
- browser/manual QA of the mounted page editor builder shell
- browser/manual QA of the future Forminator-style Vue form builder
- end-to-end form submission QA against the new Vue builder-created payloads

## Known Blockers

- module bootstrap is still in transition from manual wiring to manifest-driven loading
- old docs remain partially affected by encoding issues, so status is being centralized in newer markdown files
- some legacy builder views still exist alongside the new mounted runtime and should be consolidated
- local Artisan verification uses the bundled `tools/php/php.exe` runtime because `php` is not expected to be globally available in `PATH`
- the new forms builder runtime is production-shaped, but advanced tabs such as integrations/reports still expose partial settings rather than the full final UX

## Next Recommended Steps

1. Finish module bootstrap cleanup so all builtin modules resolve cleanly at runtime.
2. Consolidate the legacy builder screens behind the new schema-driven runtime.
3. Continue browser/manual QA for builder, Design Library, media picker and form-builder workflows.
4. Port the remaining `pb-*` partials (`video`, `divider`, `spacer`, `columns`, `container`) onto the renderer once their style options are covered by tests — done, `hero` included; covered by `BlockRenderingTest`.
5. Run `npm install` in a clean environment and execute `npm run build` before the next release tag.
