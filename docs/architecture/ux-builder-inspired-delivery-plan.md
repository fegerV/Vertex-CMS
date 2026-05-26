# UX Builder-Inspired Delivery Plan

## Goal

VertexCMS should deliver a page builder that feels as immediate and page-centric as Flatsome UX Builder, while keeping the current architecture:

- `content_json` remains the persisted source of truth
- `BlockRegistry` remains the backend source of available blocks
- public rendering remains Blade/PHP driven
- the builder stays a standalone Vue application, not a DOM-first shortcode editor

The target is not a WordPress clone. The target is a better editing experience with:

- a stronger visual canvas
- faster section/block insertion
- richer template browsing
- clearer inspector panels
- shared media and presets
- safer structured content underneath

## What Flatsome Taught Us

After reviewing the local Flatsome UX Builder files, the useful ideas are:

1. The builder should feel like a dedicated editing mode, not like a form embedded in a CRUD screen.
2. Templates and components should be first-class objects with preview metadata, categories and starter content.
3. Hover controls, add-spots and selection outlines matter as much as raw block editing.
4. Editor-shell assets and content/canvas concerns should stay separated.
5. The authoring model should be page-like and visual, even if persistence is structured.

What we should **not** copy:

- shortcode-first persistence
- WordPress-specific media/editor glue
- AngularJS runtime patterns
- DOM as the primary content contract

## Architectural Guardrails

The UX layer may evolve aggressively, but these rules stay fixed:

- persist only normalized JSON
- never make frontend preview the source of truth
- never let visual template previews bypass backend block validation
- keep block definitions registry-driven
- keep shared templates and presets backend-backed, not local-only
- keep builder-specific UX improvements additive to the current save/preview/revision pipeline

## Delivery Phases

## Phase 1

### Objective

Make the existing builder feel less like a prototype and more like a product.

### Scope

- richer template library cards
- template thumbnails and metadata
- category grouping for templates
- clearer empty states
- better quick-add previews
- normalize broken builder text/label quality where needed
- **Forms block integration**: `<x-builder.form :form-id="...">` renders `Vertex\Forms\FormPublicController::config()` JSON through a `FormRenderer.vue` component, laying groundwork for side-loaded form blocks as first-class page content alongside sections (see `docs/forminator-patterns.md §17.13` and `modules/vertex-forms/` for back-end contract).

### Backend

- enrich shared/built-in template payloads with:
  - `description`
  - `thumbnail`
  - `sections_count`
  - `blocks_count`

### Frontend

- upgrade sidebar template browsing into a visual library
- show template scope and ownership more clearly
- improve perceived quality without changing persistence

## Phase 2

### Objective

Bring the canvas interaction model closer to UX Builder.

### Scope

- stronger section hover chrome
- more obvious add-spots
- better drag/drop feedback
- slimmer, icon-first inline controls
- section and block insertion that feels immediate

### Notes

This phase is mostly UX/runtime work and should not require storage changes.

## Phase 3

### Objective

Make the inspector truly useful.

### Scope

- better grouping into `Content / Style / Advanced`
- per-block editor metadata from registry
- more visual controls for spacing, typography and surfaces
- fewer generic fields, more intent-based controls

### Backend dependency

`BlockRegistry` should continue evolving toward richer editor metadata.

## Phase 4

### Objective

Introduce stronger page composition workflows.

### Scope

- starter pages
- reusable sections
- saved block groups
- page skeletons by content type
- template ownership and governance rules

## Phase 5

### Objective

Adopt TipTap more deeply inside rich-text-capable blocks.

### Scope

- heading/text/list authoring improvements
- better inline editing ergonomics
- stronger structured rich-text payloads

## Phase 6

### Objective

Polish the builder into a stable authoring product.

### Scope

- manual QA across real pages
- keyboard and accessibility passes
- runtime performance profiling
- guardrails for large pages
- clearer error surfaces for preview/save/template failures

## Current Development Start

The current implementation pass begins with **Phase 1**:

- richer template payloads on the backend
- visual template library improvements in the Vue shell
- better documentation of the UX Builder-inspired path

This gives immediate product value without breaking the builder contract.

## Current Progress Notes

The latest builder pass has already moved part of **Phase 2** and **Phase 3** into production code:

- canvas insert slots are now stronger and more UX Builder-like
- section and block inline actions are icon-first and hover-revealed
- block inspector grouping now prefers registry metadata such as `group`, `tab`, `panel` or `section` before falling back to keyword heuristics
- inspector tabs are now resolved from backend metadata, so simple blocks can expose fewer, more relevant panels instead of forcing the same shell everywhere
- quick-add cards, block badges and placeholder previews now also read backend editor metadata, which reduces shell-only UX logic and keeps the Vue layer closer to a contract consumer
- section presets, surface tokens and section tabs now come from backend config as well, and that config is sourced from a dedicated `SectionRegistry`, which starts moving section editing toward the same registry-like contract model as blocks
- block contract normalization is now also extracted from the controller into a dedicated serializer layer, which makes the API endpoint thinner and keeps future registry evolution out of transport code
- built-in templates and shared library visibility/decoration are now extracted into a dedicated library manager, reducing controller sprawl and moving the template/preset layer toward the same explicit backend architecture
- built-in quick-add starters now also come from the backend library layer, so the most common page-start compositions are no longer hardcoded in the Vue shell
- section action bars and section context-menu actions are now backend-driven through `SectionRegistry`, instead of living as local Vue-only button maps
- block inline action bars and block context-menu commands are now backend-driven through normalized `editor.actions` and `editor.commands`, which keeps command affordances inside the builder contract layer
- block hover chrome and selection behavior now also read contract metadata through `editor.presentation`, so multi-select and toolbar visibility are no longer shell-only assumptions
- section hover chrome and selection policy now also read contract metadata through `config.sections.presentation`, which starts moving canvas behavior toward the same backend-owned UX rules as the rest of the builder
- block inline-edit affordances now also come from backend metadata through `editor.inline_editing`, which lets the shell trigger editing flows without hardcoding block-specific assumptions
- inspector ordering and primary field emphasis now also come from backend field metadata, which helps the builder surface the most important controls first in a more UX Builder-like way
- inspector field layout now also comes from backend metadata, so compact rows and media-first sections are no longer hardcoded view decisions
- inspector control variants now also come from backend metadata, so segmented choices, color swatches, spacing sliders and link-focused inputs are no longer local Vue-only widget decisions
- inspector control families now also come from backend metadata, so higher-level UX groupings like Typography, Surface, Spacing and Link are no longer shell-authored presentation guesses
- inspector control packs now also come from backend metadata, so richer editing sections like Typography packs, Button treatment packs and Media settings packs are no longer assembled ad hoc in Vue
- block-level pack recipes now also come from backend metadata through `editor.packs`; the full current block catalog declares them directly in `blocks.php`, while `BlockPackRegistry` remains only as a temporary duplicated catalog during cleanup
- legacy mojibake strings were removed from the active builder SFC layer
- the live canvas runtime now prefers explicit dirty signals over deep-watching the full page tree, and preview/autosave skip duplicate JSON payloads to keep large pages more responsive during inspector edits
- the live preview endpoint now avoids rendering both public HTML and editor document in the same request, while the server renderer avoids collection-heavy loops on the hot preview path

The next logical iteration is to shrink or remove `BlockPackRegistry`, then move into the dedicated design-library workspace for templates, starters and reusable sections.
