# Page Builder Architecture

## Goal

VertexCMS should keep the page builder as a schema-oriented, contract-driven system centered on structured JSON, not a DOM-first visual editor and not raw HTML blobs. The recommended editor stack is:

- `@tiptap/vue-3` for rich-text editing
- custom Vue 3 block components for layout/content blocks
- JSON as the persisted page contract
- Blade + a renderer/mapper layer for frontend output
- Pinia for editor state
- Yjs as an optional future layer for collaboration

This fits the current repository direction better than replacing the builder with a monolithic HTML editor.

## Transitional Runtime Note

The advanced builder shell now lives in compiled Vue SFCs:

- `resources/js/admin/builder/AdvancedBuilderApp.vue`
- `resources/js/admin/builder/components/BuilderBlockRenderer.vue`
- `resources/js/admin/builder/components/BuilderBlockSettings.vue`
- `resources/js/admin/builder/components/BuilderSectionSettings.vue`

Blade is now only a host layer that passes mount data and route context.

The repository still keeps the compiler-enabled Vue alias for other legacy admin screens that mount inline Vue from Blade. The builder itself should no longer rely on runtime template compilation or a CSP `unsafe-eval` exception.

## Architectural Rules

- persisted page content stays JSON-first in `content_json`
- registry metadata is the source of truth for available blocks
- contracts are more important than a specific editor implementation
- frontend rich-text is a sub-tool inside blocks, not the builder architecture itself
- Blade/PHP rendering stays authoritative for public output
- client-side schema validation improves UX, but backend validation remains final

## Recommended Stack

| Layer | Technology | Why |
| --- | --- | --- |
| Rich text engine | `@tiptap/vue-3` / ProseMirror | Vue 3 native, JSON/HTML output, extensible, headless-friendly |
| Block system | Custom Vue components + JSON schema | Nested layouts, reusable blocks, strict validation |
| Persistence | `content_json` in DB | Versionable, diffable, API-safe, renderer-independent |
| Frontend rendering | Blade + block/component mapper | SSR-friendly, Tailwind-friendly, low frontend overhead |
| Editor state | Pinia | Predictable state, undo/redo coordination, registry-backed UI |
| Realtime collaboration | Yjs (optional) | Future-ready collaboration without forcing complexity now |

## Why This Fits VertexCMS

The repo already contains the foundations this approach needs:

- builder content is stored in `content_json`
- block definitions already live in `App\Builder\Config\BlockRegistry`
- rendering already goes through `PageBuilderService` and `PageRenderer`
- TipTap packages are already present in `package.json`

That means the best path is not a replacement of the builder model, but an upgrade of the authoring experience inside the existing JSON/block contract.

## Target Content Model

The long-term persisted page contract should stay JSON-first:

```json
{
  "version": "1.0",
  "layout": "default",
  "sections": [
    {
      "id": "section_hero",
      "settings": {
        "container": "wide"
      },
      "blocks": [
        {
          "id": "block_heading",
          "type": "heading",
          "settings": {
            "content": {
              "type": "doc",
              "content": [
                {
                  "type": "heading",
                  "attrs": { "level": 1 },
                  "content": [
                    { "type": "text", "text": "Welcome" }
                  ]
                }
              ]
            }
          }
        }
      ]
    }
  ]
}
```

Important detail:

- layout and structural blocks remain custom JSON
- rich text inside blocks may be stored as TipTap/ProseMirror JSON
- frontend rendering should never depend on the editor runtime

## Editor Responsibilities

### TipTap

TipTap should handle:

- headings
- paragraphs
- lists
- links
- inline emphasis
- inline images where appropriate

TipTap should not become the full page builder by itself. It is the rich-text engine used inside specific blocks.

### Custom block layer

The block layer should handle:

- hero
- CTA
- FAQ
- galleries
- columns
- embeds
- forms
- media cards
- reusable section templates

This preserves the current VertexCMS strength: structured page composition instead of editor-centric HTML.

## Block Registry Contract

`BlockRegistry` should remain the source of truth for available blocks, but should evolve to include editor metadata in addition to render metadata.

Recommended fields per block:

```php
[
    'label' => 'Heading',
    'category' => 'content',
    'icon' => 'heading',
    'template' => 'builder.blocks.heading',
    'schema' => [
        'content' => ['type' => 'tiptap', 'required' => true],
        'align' => ['type' => 'string', 'default' => 'left'],
    ],
    'editor' => [
        'component' => 'builder-heading-block',
        'supports' => ['rich_text', 'spacing', 'color'],
    ],
]
```

This lets VertexCMS use one registry for:

- API exposure
- admin builder palette
- validation
- rendering
- future marketplace extension

Sections should follow the same idea instead of staying as ad-hoc controller arrays. The repository now also has a dedicated `SectionRegistry`, so section tabs, presets, defaults and surface tokens can evolve as contract data instead of being hardcoded in the Vue shell.

The same separation is now applied to the builder library layer: built-in templates, shared presets/templates visibility, and thumbnail decoration no longer live inside the controller body. They are handled by a dedicated `BuilderLibraryManager`, which keeps the admin endpoint thin and makes the library contract independently testable.

That same library layer now also owns built-in quick-add starter templates. The Vue shell consumes them through `config.quick_add.templates`, instead of hardcoding starter compositions locally.

The library layer now also exposes a dedicated design-library workspace contract through `admin.pages.builder.design-library.index`. It groups templates, quick-start compositions and block presets into a single backend-owned payload with navigation counts, category summaries, collection metadata and empty-state copy. This gives the future Vue/Inertia design-library screen one stable API instead of forcing it to stitch together separate endpoints.

The current builder API now already moves in this direction:

- `/admin/api/builder/blocks` exposes a keyed `blocks` map for runtime usage
- the same endpoint also exposes `entries` as a list for compatibility
- the controller is now only a transport layer; block contract normalization itself lives in a dedicated `BuilderContractSerializer`
- field metadata is normalized before it reaches the Vue shell
- `group` metadata is inferred when block config does not define it explicitly
- `editor.tabs` and `editor.inspector.default_tab` are now resolved from normalized field groups, so the inspector shell can stay contract-driven instead of hardcoding the same tabs for every block
- block-level editor metadata is now also normalized for `preview.badge`, `preview.empty_state`, `quick_add.hint`, `quick_add.keywords`, and basic `capabilities`, so canvas and library UX can evolve without hardcoding every affordance in Vue
- block action panels and block context-menu commands are now also contract-backed through normalized `editor.actions` and `editor.commands`, so inline controls can evolve without pushing product rules into the Vue shell
- block selection mode, toolbar visibility and preview behavior now also flow through normalized `editor.presentation`, so hover chrome and multi-select behavior can stay backend-owned instead of being scattered through the shell
- block inline editing affordances now also flow through normalized `editor.inline_editing`, so canvas triggers like double-click, target inspector tab and primary editing intent are backend-owned metadata too
- field ordering and primary inspector emphasis now also flow through normalized field metadata like `priority` and `importance`, so the inspector can present the right controls first without hardcoded Vue sorting
- field layout primitives now also flow through normalized field metadata like `layout.variant`, `layout.span` and `layout.row`, so the inspector can compose compact two-up rows, media-first cards and stacked fields from backend contract data
- field control presets now also flow through normalized field metadata like `control.variant`, `control.min`, `control.max`, `control.step` and `control.unit`, so the inspector can render segmented choices, color swatches, spacing sliders and link-oriented inputs from backend contract data instead of shell heuristics
- field control families now also flow through normalized field metadata like `control.family`, `control.family_label` and `control.family_icon`, so the inspector can introduce higher-level UX groupings such as Typography, Surface, Spacing and Link without inventing its own sidebar semantics
- field control packs now also flow through normalized field metadata like `control.pack`, `control.pack_label`, `control.pack_description` and `control.pack_icon`, so the inspector can render composite editing sections such as Button treatment, Media settings or Typography packs from backend contract data
- block-level pack recipes now also flow through `editor.packs`; the full current block catalog declares these bundles inside `blocks.php`, while `BlockPackRegistry` remains only a compatibility reader over the primary registry
- section-level builder config now also flows through `config.sections`, sourced from `SectionRegistry`, including section tabs, default settings, surface tokens and starter presets, so section UX can follow the same contract-driven direction as blocks
- section action panels and section context commands now also come from `SectionRegistry`, so section toolbar affordances follow the same backend-owned contract model as blocks
- section selection policy, toolbar visibility and canvas affordances now also flow through `config.sections.presentation`, which keeps section hover/selection behavior aligned with the same contract-driven model
- quick-add starter templates now flow through `config.quick_add.templates`, sourced from `BuilderLibraryManager`, so starter compositions are backend-backed contract data as well
- design-library workspace data now flows through `BuilderLibraryManager::designLibraryWorkspace()`, giving templates, starters and presets a single backend-owned browsing contract

That means the inspector can gradually become fully registry-driven without waiting for a full rewrite of every legacy block definition.

## Rendering Strategy

Frontend rendering should stay server-first:

1. page loads from DB with `content_json`
2. renderer iterates through sections/blocks
3. each block resolves through a Blade template or renderer class
4. only interactive blocks hydrate client-side when needed

That preserves:

- SSR/SEO quality
- fast first render
- shared-hosting compatibility
- headless/API consistency

## Validation

Validation should remain schema-first and happen before persistence.

Recommended layering:

- backend normalization in `PageBuilderService`
- backend schema validation per block
- optional frontend validation mirrors for better UX
- future Zod-based client validation only as a convenience layer, not the source of truth

The backend must still be authoritative.

## State Management

Recommended editor state responsibilities for Pinia:

- current page structure
- selected section/block
- undo/redo snapshots
- autosave state
- preview state
- shared preset/template library state
- dirty flags and optimistic save status

Yjs should stay optional until multi-user editing is a real product requirement.

## Shared Hosting Compatibility

This stack works well on shared hosting because:

- TipTap runs fully in the browser
- persisted content is plain JSON in the database
- frontend rendering happens in Blade/PHP
- no Redis or queue is required just to edit content

Optional enhancements like realtime collaboration or background media processing should degrade gracefully.

## Delivery Plan

### Step 1

Adopt TipTap as the standard rich-text engine for text-capable blocks.

### Step 2

Evolve `BlockRegistry` to include editor schema and component metadata.

### Step 3

Move advanced builder state into a dedicated Pinia store.

### Step 4

Keep Blade rendering as the primary frontend path, with lazy hydration for interactive blocks.

### Step 5

Add optional collaboration with Yjs only after the single-user editor flow is stable.

## Current Repo Notes

Autosave is now durable: after backend validation, the editor stores the normalized
page content and its recovery revision in one database transaction. A successful
autosave therefore survives a refresh or browser crash instead of existing only in
the revision list, while invalid block contracts leave both the page and revisions
untouched.

The repository already includes:

- `@tiptap/vue-3`
- `@tiptap/starter-kit`
- `@tiptap/extension-link`
- `@tiptap/extension-image`

There is also an early Vue prototype under `resources/js/components/builder/`, but it should be treated as exploratory code until it is aligned with the current production builder contract and block inventory.

For the broader admin UX direction around page creation and the parallel forms-builder strategy, see:

- `docs/architecture/wp-style-editor-and-form-builder.md`
- `docs/architecture/ux-builder-inspired-delivery-plan.md`
- `docs/architecture/breakdance-inspired-vertex-builder.md`

## UX Builder-Inspired Shell

The current strategic direction for the advanced builder UI is:

- keep the structured JSON/block contract
- keep backend rendering authoritative
- borrow the best interaction patterns from Flatsome UX Builder
- improve the Vue shell phase by phase instead of replacing the persistence model

That means:

- stronger template browsing
- better add-spots and hover controls
- more page-like preview on canvas
- a more useful inspector

without switching to shortcode storage or DOM-first editing.

## Decision

Use TipTap for rich text inside the existing JSON/block-based page builder, not as a replacement for the builder architecture itself.
# Global design system

The builder contract now includes `design_system`, a normalized set of global
color, typography, container, section-spacing and button tokens. Editors manage
the values in **Settings → Global design**; the public page shell emits the same
sanitized tokens as CSS custom properties. This keeps the builder preview and
published output on one brand foundation while allowing individual blocks to
override a token when a composition requires it.
