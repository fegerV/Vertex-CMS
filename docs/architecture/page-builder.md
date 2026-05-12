# Page Builder Architecture

## Goal

VertexCMS should keep the page builder centered on structured JSON, not raw HTML blobs. The recommended editor stack is:

- `@tiptap/vue-3` for rich-text editing
- custom Vue 3 block components for layout/content blocks
- JSON as the persisted page contract
- Blade + a renderer/mapper layer for frontend output
- Pinia for editor state
- Yjs as an optional future layer for collaboration

This fits the current repository direction better than replacing the builder with a monolithic HTML editor.

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

The repository already includes:

- `@tiptap/vue-3`
- `@tiptap/starter-kit`
- `@tiptap/extension-link`
- `@tiptap/extension-image`

There is also an early Vue prototype under `resources/js/components/builder/`, but it should be treated as exploratory code until it is aligned with the current production builder contract and block inventory.

For the broader admin UX direction around page creation and the parallel forms-builder strategy, see:

- `docs/architecture/wp-style-editor-and-form-builder.md`

## Decision

Use TipTap for rich text inside the existing JSON/block-based page builder, not as a replacement for the builder architecture itself.
