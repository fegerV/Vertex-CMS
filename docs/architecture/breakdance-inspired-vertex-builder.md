# Breakdance-Inspired Vertex Builder Plan

## Purpose

This document captures the useful patterns found in the local `breakdance/` reference and turns them into an implementation plan for Vertex Builder.

The goal is not to copy Breakdance or its WordPress runtime. VertexCMS should keep its stronger native direction:

- `content_json` is the persisted source of truth.
- Laravel validation and Blade rendering stay authoritative.
- Vue is the authoring shell, not the public rendering dependency.
- Builder metadata is a backend-owned contract consumed by the editor.

## Breakdance Strengths

The local `breakdance/` folder shows several product and architecture strengths worth adopting:

- Dedicated builder applications: Breakdance ships separate entry points for the main app, design library, template management, onboarding and tooling. Vertex should follow this by treating the builder as a focused workspace with smaller app surfaces, not as a page form with extra controls.
- Metadata-driven controls: Breakdance UI controls are described through structured options, conditions, repeaters, dropdown sources and action handlers. Vertex already moves this way through `BuilderContractSerializer`; the next step is to reduce shell-only heuristics further.
- Visual design library: templates are not just JSON snippets. They carry thumbnails, categories, author/source metadata and preview flows. Vertex already has `BuilderLibraryManager`; it should grow toward a richer library browser and governance layer.
- Canvas-first editing: selection, hover chrome, add controls and direct interaction with the preview are central. Vertex live preview bridge already supports selection and add/action messages; it should keep moving toward immediate, page-like editing.
- Onboarding and starter flows: Breakdance has a guided setup surface. Vertex should add builder onboarding through starter pages, section presets and "first page" recipes rather than generic empty states.
- Control families and packs: Breakdance groups editing work by intent. Vertex should keep exposing `editor.packs`, `control.family` and `control.pack` from the backend contract so the inspector can stay semantic.
- Tooling separation: cache regeneration, template management and design-library flows are separate from the main canvas. Vertex should avoid growing one giant builder screen for every auxiliary workflow.

## Patterns To Avoid

These Breakdance patterns do not fit VertexCMS:

- WordPress AJAX actions as the main integration model.
- DOM-first or editor-runtime-first persistence.
- Public rendering that depends on the builder app.
- Large opaque bundled runtime as the only source of product rules.
- WordPress-specific media, post and shortcode assumptions.

## Current Vertex Strengths

Vertex Builder is already ahead in several core areas:

- JSON-first persistence through `content_json`.
- Server-side renderer through `App\Builder\Services\PageRenderer`.
- Registry-driven blocks through `App\Builder\Config\BlockRegistry`.
- Contract serialization through `App\Builder\Support\BuilderContractSerializer`.
- Section metadata through `SectionRegistry`.
- Shared presets/templates through `BuilderLibraryManager`.
- Autosave, preview, revisions, import/export and template application.
- Live preview bridge for selection, block actions and add controls.
- Backend validation remains final before persistence.

This is the right foundation. The best path is to deepen it, not replace it.

## Gap Analysis

- Block editing metadata now has a single primary source: every block exposes explicit `editor.packs` through `app/Builder/Config/blocks.php`, while `BlockPackRegistry` is only a compatibility reader over that registry.
- Auxiliary builder flows such as template management and onboarding are not yet first-class standalone workspaces.
- The visual template library exists, but can become closer to a real design library with stronger preview and category workflows.
- Inspector control packs exist for the full current block catalog.
- Large-page performance has started improving, but needs continued profiling around live preview refresh, autosave and inspector edits.
- Runtime/manual QA is still required for full builder confidence.

## Implementation Plan

### Phase 1: Contract Completeness

- Keep enriching `BuilderContractSerializer` and `BlockRegistry` so every block exposes semantic `editor.packs`.
- Prefer explicit block metadata where possible.
- Keep `BlockPackRegistry` as a thin compatibility reader only; new pack metadata belongs in `app/Builder/Config/blocks.php`.
- Add tests around pack metadata for content, layout, dynamic, interactive and utility blocks.

### Phase 2: Design Library Workspace

- Split template management into a clearer dedicated builder library surface.
- Keep templates backend-backed through `BuilderLibraryManager`.
- Add stronger category filters, visual previews, ownership labels and source labels.
- Expose one design-library workspace contract for templates, quick-start compositions and presets before building the richer Vue/Inertia browser.
- Keep template application routed through backend validation.

### Phase 3: Canvas Interaction

- Continue improving live preview selection, add controls and inline actions.
- Make the canvas use backend `presentation`, `actions`, `commands` and `inline_editing` metadata wherever possible.
- Keep public Blade output as the preview source so the editor sees real rendering.

### Phase 4: Starter And Onboarding Flows

- Add starter page recipes for common site types.
- Add empty-state onboarding that creates real JSON sections through the same builder contract.
- Reuse templates and quick-add recipes instead of hardcoding starter structures in Vue.

### Phase 5: Inspector Refinement

- Promote control packs into the primary editing model.
- Add richer controls for spacing, surface, typography, media and behavior.
- Use field metadata for layout, priority and importance.

### Phase 6: Production Hardening

- Run manual QA for create, edit, autosave, preview, publish, restore and public render.
- Profile large pages and repeated preview updates.
- Add clearer error states for save, preview and template operations.
- Keep browser-level tests focused on high-value authoring flows once the UI stabilizes.

## Completed Implementation Slices

### Slice 1: Pack Catalog Coverage

The first implementation slice expanded block pack recipes across the catalog and lets `BuilderContractSerializer` normalize those recipes into the runtime contract.

Why this slice:

- It immediately improves the runtime builder contract.
- It does not change persistence.
- It does not depend on a risky UI rewrite.
- It prepares the inspector to behave more like a semantic Breakdance-style control system.
- It keeps explicit `BlockRegistry` migration available as a later mechanical cleanup.

### Slice 2: Clean Block Catalog

The second implementation slice rewrote `app/Builder/Config/blocks.php` into a clean ASCII block catalog:

- All current block types were preserved.
- Block names, descriptions, defaults, field labels and option labels were normalized.
- `editor.packs` now live directly on block definitions.
- The catalog documents itself as the backend source of truth for builder availability and metadata.
- Existing save, preview, revisions, library and registry API tests stay green.

### Slice 3: Design Library Workspace UI

The third implementation slice connects the backend design-library workspace contract to a dedicated Vue screen:

- `/admin/pages/builder/design-library` is now a first-class builder workspace surface.
- `/admin/api/pages/builder/design-library` keeps the JSON workspace contract separate from the HTML shell.
- Templates, quick-start compositions and block presets are browsable through one visual library with stats, category filters, search and preview cards.
- Shared templates and presets can be managed from the workspace when the current user owns them or has super-admin access.
- The main advanced builder toolbar links to the design library and can pass `page_id` so visible templates can be merged back into the current page through the existing backend validation route.

### Slice 4: Live Canvas Section Controls

The fourth implementation slice moves the live preview closer to a Breakdance-style direct-manipulation canvas:

- the iframe bridge now renders a dedicated floating section toolbar in addition to block controls;
- section controls support quick block insertion, inserting a new section, duplicate, move up/down and delete;
- the parent Vue shell handles `section-action` messages through the same normalized section/block state pipeline used by the editable canvas;
- new sections created from the live preview immediately open quick-add so composition stays canvas-first;
- public Blade rendering remains the preview source, and `content_json` stays the only persisted contract.

## Next Recommended Slice

Continue Breakdance-style direct manipulation by improving inline text editing and responsive canvas chrome while keeping `content_json` and backend validation as the source of truth.
