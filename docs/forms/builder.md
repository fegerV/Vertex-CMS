# Vertex Forms Builder

## Status

Last updated: `2026-05-13`

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

What is still missing for the target UX:

- proper top toolbar with draft/publish/autosave state
- multi-tab form settings flow
- form templates picker
- polished field grouping and search
- drag-and-drop reorder ergonomics
- conditional logic builder UI
- submissions/reporting workspace
- integrations workspace
- appearance presets UI
- email notification flow editor
- production-grade preview/publish flow

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

### Stage 3

- add conditional logic builder
- add templates/presets/import-export
- add reporting/submissions workspace

### Stage 4

- add integrations, payments, calculators, multi-step polish

## References

- Forminator docs: <https://wpmudev.com/docs/wpmu-dev-plugins/forminator/>
