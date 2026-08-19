# Team Vertex-CMS Log

## [2026-05-07] MVP Phase 1 Improvements

### Completed
- **Data-driven `robots.txt`**: Implemented `seo.robots_txt` setting in the database and updated `RobotsController` to use it. Added feature tests. (Engineer)
- **Admin UI Polish**: 
    - Added Heroicons to sidebar navigation.
    - Improved mobile responsiveness with a slide-over menu.
    - Added "View Site" link to the header.
    - Implemented dynamic breadcrumbs with Russian translations. (UI Engineer)
- **Block Template Library**: Created 26+ Blade templates for all major block types (heading, text, image, gallery, interactive components, etc.) in `resources/views/builder/blocks/`. Integrated Alpine.js for interactivity. (Engineer)

### In Progress
- **Multi-language (i18n) Infrastructure**: Setting up Laravel localization and multi-language content support for pages. (Engineer)
- **UI Modernization & Dark Mode**: Implementing a sleek dark theme and modernizing overall UI components. (UI Engineer)
- **Page Builder UX Enhancement**: Adding Undo/Redo, search functionality, and improved canvas visualization. (UI Engineer)
- **Comprehensive MVP Audit**: QA-engineer is performing an end-to-end audit of the installer, permissions, and media manager. (QA Engineer)

### Upcoming
- Full end-to-end verification of the "Install -> Create -> Publish" flow.
- Integration of the modernized UI with the new block templates.

## [2026-05-13] Module System + Builder Registry Pass

### Completed
- **Module tiers foundation**: Added config-driven tier metadata for `core`, `builtin`, and `marketplace` modules, plus loader/catalog/manager classes for `module.json` discovery. (Engineer)
- **Security module planning slice**: Added architecture and rollout docs for `vertex-security` plus a module skeleton for future runtime integration. (Engineer)
- **Builder architecture formalized**: Documented `TipTap + JSON block builder + Blade renderer` as the target page-builder stack. (Engineer)
- **Vue builder prototype normalized**: Replaced the exploratory prototype with a registry-driven version that no longer depends on missing `pinia` or `sortablejs` packages and can mount from the main JS bundle. (Engineer)
- **Builder registry API contract**: Extended `/admin/api/builder/blocks` with normalized editor metadata and added a feature test for the contract. (Engineer)

### In Progress
- **Full module bootstrap cleanup**: Manifest-driven loading is partially in place, but final provider/runtime activation still needs another pass. (Engineer)
- **Production builder integration**: The improved Vue/TipTap prototype exists, but is not yet the default admin page-builder runtime. (UI Engineer)

### Verification
- PHP syntax checks passed for the new module registry and builder registry related files.
- Isolated module manifest discovery confirmed detection of `vertex-core`, `vertex-auth`, `vertex-content`, `vertex-forms`, and `vertex-security`.
- `BuilderContractTest` passed: `4 tests`, `29 assertions`.
- `BuilderRegistryApiTest` passed: `1 test`, `339 assertions`.
- `npm run build` passed with the updated Vue/TipTap prototype.
- Full Laravel test suite still needs rerun after the remaining bootstrap cleanup.
