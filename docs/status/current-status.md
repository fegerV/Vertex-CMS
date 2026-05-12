# Current Status

## Updated

Last updated: `2026-05-13`

## What Is Implemented

### Core product slices already in code

- installer flow foundation
- admin auth and RBAC
- pages CRUD
- JSON-based page builder foundation
- media library
- SEO metadata, sitemap, robots
- API v1 foundation
- AI draft module foundation
- PWA/theme/taxonomy foundation
- analytics foundation

### Module system progress

- builtin module folders exist under `modules/`
- `vertex-forms` exists as a real builtin module
- `vertex-security` now has a planning and skeleton module
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

It still needs broader manual QA, but it is no longer just an isolated prototype.

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
- builder runtime parity pass now also passes:
  - `npm run build`
  - `BuilderRegistryApiTest`: `1 passed`, `339 assertions`
  - `BuilderContractTest`: `4 passed`, `29 assertions`
  - `php artisan view:cache`
- builder registry API test was added in:
  - `tests/Feature/BuilderRegistryApiTest.php`
- form field registry API test was added in:
  - `tests/Feature/FormFieldRegistryApiTest.php`

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

- full `php artisan test` run across the whole suite after broader runtime cleanup
- browser/manual QA of the mounted page editor builder shell
- browser/manual QA of the future Forminator-style Vue form builder
- end-to-end form submission QA against the new Vue builder-created payloads

## Known Blockers

- module bootstrap is still in transition from manual wiring to manifest-driven loading
- old docs remain partially affected by encoding issues, so status is being centralized in newer markdown files
- some legacy builder views still exist alongside the new mounted runtime and should be consolidated
- local runtime verification currently relies on a direct vendor autoload patch because `composer dump-autoload` was not available in this environment
- the new forms builder runtime is production-shaped, but advanced tabs such as integrations/reports still expose partial settings rather than the full final UX

## Next Recommended Steps

1. Finish module bootstrap cleanup so all builtin modules resolve cleanly at runtime.
2. Run the full Laravel test suite again and record the updated count/assertions.
3. Consolidate the legacy builder screens behind the new schema-driven runtime.
4. Expand frontend rendering parity so more backend-defined block types have first-class public renderers, not only editor schemas.
5. Replace the current `vertex-forms` Alpine builder with the Forminator-style Vue app defined in the new architecture spec.
