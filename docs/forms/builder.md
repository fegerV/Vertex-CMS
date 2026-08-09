# Vertex Forms Builder

## Status

Last updated: `2026-08-07`

The `vertex-forms` module already has:

- backend form CRUD
- field persistence and validation
- a dedicated Vue-based builder runtime
- frontend form rendering

The current builder UI is much closer to the target architecture, but it is **not yet** the final production authoring experience.

It should now be treated as:

- a working registry-driven builder foundation
- suitable for real integration work
- not yet equivalent to the full Forminator feature surface

## Current Reality In Repo

Current builder screen:

- file: `modules/vertex-forms/resources/views/admin/forms/builder.blade.php`
- stack: Blade shell + Vue 3 runtime
- runtime entry: `resources/js/admin/forms/mountFormBuilder.js`
- app component: `resources/js/admin/forms/FormBuilderApp.vue`

What already works conceptually:

- create a form
- add fields
- select a field
- edit registry-driven field properties
- duplicate/delete fields
- save JSON payload to `/admin/api/forms`
- load formal field metadata from `/admin/api/forms/field-registry`
- apply Forminator-like starter templates
- autosave existing forms after edits
- use top-level workspaces for Build, Appearance, Behavior, Emails, Integrations, Visibility, Submissions and Reports
- configure multi-rule field conditional logic through UI instead of raw JSON
- use an expanded field catalog including URL, time, structured name, address, consent and rating fields
- inspect recent submissions and analytics from inside the builder workspace

What is still missing for the target UX:

- multi-notification email routing and test-send workflow
- production-grade preview/publish flow

The canvas now supports native drag-and-drop field reordering while retaining
the accessible up/down controls for keyboard users. Production hardening also
adds atomic submission persistence, private file storage by default, automatic
version snapshots with retention, and enforced public availability rules.
Conditional fields hidden by server-side rules are no longer persisted even
when a client submits forged values, and multi-file fields now validate and
store every upload using the same private-storage policy.

Production P0 protections now include server-side reCAPTCHA v2/v3 and
Cloudflare Turnstile verification, per-form/IP rate limiting, idempotent
submission retries, and permission-gated downloads for private submission
files.

P1 work has started with a working webhook integrations workspace, encrypted
signing secrets, queued signed delivery with retry/backoff and delivery logs.
The email workspace now maps its admin-recipient and autoresponder settings to
the backend notification flow. Builder edits also have a 50-step undo/redo
history with toolbar controls and Ctrl/Cmd+Z shortcuts.

P2 work now includes value-aware submission search, status filters and bulk
actions, memory-safe filtered CSV streaming, privacy retention with private-file
cleanup, anonymization support, and analytics based on recorded form views
instead of synthetic placeholder traffic.

## Product Direction

The target experience for `vertex-forms` is now defined as:

- WordPress-friendly for familiarity
- Forminator-like for form creation workflow
- Vue 3 driven for long-term maintainability
- JSON-schema and registry driven for backend consistency

Detailed UX/product architecture lives in:

- `docs/architecture/wp-style-editor-and-form-builder.md`

## Target Builder Architecture

### UI stack

- Vue 3 for the builder app
- Pinia for builder state
- registry-driven field catalog
- Blade shell for mounting and permissions

### Data contract

Persisted form contract should stay JSON-first:

```json
{
  "version": 1,
  "type": "standard",
  "status": "draft",
  "name": "Contact Form",
  "description": "Lead capture form",
  "fields": [
    {
      "id": "field_name",
      "type": "text",
      "name": "full_name",
      "label": "Full Name",
      "required": true,
      "settings": {
        "placeholder": "John Smith"
      }
    }
  ],
  "settings": {
    "submit_label": "Send",
    "success_message": "Thank you"
  }
}
```

### Registry contract

Each field type should expose:

- label
- category
- icon
- defaults
- validation schema
- inspector schema
- frontend renderer key
- submission normalization rules

This mirrors the same direction already used in the page builder registry work.

## Planned Screens

### 1. Forms list

- overview cards
- search/filter
- bulk actions
- stats columns
- draft/published badges

### 2. Template picker

- blank form
- contact form
- lead form
- quote request
- booking request
- newsletter
- calculator
- multi-step wizard

### 3. Builder editor

- left sidebar: field palette + search + categories
- center: canvas + inline add points + page/step navigation
- right sidebar: inspector tabs
- top tabs: Build, Appearance, Behavior, Emails, Integrations, Visibility, Reports

### 4. Submissions/reporting

- entries table
- filters
- CSV export
- conversion metrics
- source/referrer summary

## Implementation Stages

### Stage 1

- keep current backend contract
- add Vue builder shell
- move field registry metadata to a shared API payload

Stage 1 is now implemented.

### Stage 2

- replace Alpine prototype with Vue builder runtime
- add autosave, draft/publish, settings tabs

Stage 2 is partially implemented: the Vue builder now has autosave for existing forms, a stronger toolbar, starter templates, top-level workspaces, canvas summaries, a visual multi-rule conditional logic editor, and embedded submissions/analytics panels.

### Stage 3

- add conditional logic builder
- add templates/presets/import-export
- add reporting/submissions workspace

### Stage 4

- add integrations, payments, calculators, multi-step polish

## References

- Forminator docs: <https://wpmudev.com/docs/wpmu-dev-plugins/forminator/>
