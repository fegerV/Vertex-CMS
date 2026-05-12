# WordPress-Style Editor And Form Builder

## Goal

VertexCMS can absolutely use a WordPress-familiar creation experience without copying WordPress's technical debt.

The right approach is:

- keep Laravel data contracts and module architecture
- keep JSON-first storage
- adopt a WordPress-like information architecture for the editor
- adopt a Forminator-like workflow for form creation

This gives users a familiar CMS feel while preserving VertexCMS performance and maintainability.

## Product Decision

VertexCMS should separate authoring UX into two related but distinct editors:

1. `Page Editor`
   Use a WordPress-like create/edit screen with a central content workspace and a right-side publish/settings rail.
2. `Form Builder`
   Use a Forminator-like builder app with a field palette, canvas, inspector, and top-level settings tabs.

## Why This Is The Right Fit

### WordPress familiarity helps adoption

Editors coming from WordPress expect:

- title field first
- permalink visible near the top
- publish controls on the right
- page attributes and SEO grouped into cards
- a clear distinction between content editing and document settings

### Forminator is a better model for forms than generic page editing

Forms are not just content blocks. They need:

- field-level configuration
- validation settings
- notifications
- integrations
- visibility/limits
- submissions and conversion tracking

That workflow is much closer to Forminator than to the page builder.

## Current Repo Mapping

### Pages

Current files:

- `resources/views/admin/pages/create.blade.php`
- `resources/views/admin/pages/edit.blade.php`
- `resources/views/admin/pages/partials/form.blade.php`

Current state:

- backend page CRUD is real
- `content_json` contract is real
- SEO, taxonomies, custom fields and template controls already exist
- current create/edit screen is more linear than WordPress-like

### Forms

Current file:

- `modules/vertex-forms/resources/views/admin/forms/builder.blade.php`

Current state:

- builder prototype exists
- backend save contract exists
- current UI is too early/simple for production use

## Target 1: WordPress-Style Page Editor

### Page layout

Use a 2-column admin editor shell:

- main column: `minmax(0, 1fr)`
- right rail: `360px`

Desktop structure:

1. Header bar
   Page title, status badge, preview, save draft, publish/update.
2. Main column
   Title, permalink, editor mode tabs, content editor.
3. Right rail
   Publish, attributes, featured image, taxonomy, SEO, template/settings cards.

Mobile structure:

- right rail collapses into accordions under the editor
- sticky bottom action bar for `Save Draft`, `Preview`, `Publish`

### Main column sections

#### 1. Title and permalink

Exactly near the top:

- page title input
- generated permalink preview
- quick edit slug action

#### 2. Mode tabs

Top-level page modes:

- `Editor`
- `Builder`
- `SEO Preview`

Behavior:

- `Editor` shows TipTap/block text authoring
- `Builder` shows block/layout composition UI
- `SEO Preview` shows Google/OG snippet preview

#### 3. Content workspace

Recommended composition:

- TipTap for textual blocks
- block inserter for layout/content sections
- inline `Add block` affordances
- left block outline for long pages

### Right rail cards

#### Publish

- status: draft/published/scheduled
- visibility
- publish date
- author
- preview button
- primary action: `Publish` or `Update`

#### Page attributes

- parent page
- template
- sort order

#### Featured image

- media picker
- alt/status hints

#### Taxonomies

- category/tag style selectors

#### SEO

- title
- description
- canonical
- robots
- OG image
- schema status

#### Advanced

- custom fields summary
- page flags
- diagnostics

## Target 2: Forminator-Style Forms Builder

### Builder shell

Use a dedicated builder app with this structure:

1. Top app bar
2. Left field library
3. Center canvas
4. Right inspector
5. Secondary settings tabs

### Top app bar

The top bar should contain:

- form name
- status badge
- autosave state
- preview
- save draft
- publish / publish changes
- more actions menu

More actions:

- duplicate
- export JSON
- import template
- unpublish
- delete

### Secondary tabs

Tabs should be:

- `Build`
- `Appearance`
- `Behavior`
- `Emails`
- `Integrations`
- `Visibility`
- `Submissions`
- `Reports`

This is the key difference from the current prototype. The builder must not stop at field editing only.

### Left field library

Field palette should support:

- search
- category grouping
- favorites/recent
- click to insert
- drag to insert

Recommended categories:

- Basic
- Choice
- Advanced
- Payment
- Layout
- Hidden/System

Recommended field inventory for v1:

- text
- email
- phone
- number
- textarea
- select
- radio
- checkbox
- date
- file
- hidden
- heading
- html
- divider
- page-break
- consent
- calculator

### Center canvas

The canvas should feel like a visual form document, not a JSON list.

Needed behaviors:

- empty-state CTA: `Insert Fields`
- drag reordering
- inline add buttons between fields
- selected field highlight
- multi-page step navigation
- submit button block rendered in-place
- device preview widths

### Right inspector

Inspector tabs per field:

- `Field`
- `Validation`
- `Logic`
- `Appearance`
- `Advanced`

Examples:

- `Field`: label, name, placeholder, help text
- `Validation`: required, min/max, regex, allowed types
- `Logic`: conditional show/hide rules
- `Appearance`: width, classes, icon, description placement
- `Advanced`: default values, hidden values, admin labels, tracking keys

### Form-level tabs

#### Appearance

- theme preset
- labels layout
- spacing
- button style
- progress bar style
- success state

#### Behavior

- honeypot
- Turnstile/reCAPTCHA
- login requirement
- schedule window
- submission limits
- save draft/resume later
- redirect/thank-you behavior

#### Emails

- admin notifications
- auto-reply
- routing rules
- PDF attachment toggle

#### Integrations

- webhook
- email marketing
- CRM
- payment provider

#### Visibility

- publish state
- role restrictions
- geo/device/referrer rules

#### Submissions

- entries table
- spam flagging
- export
- retention rules

#### Reports

- views
- submissions
- conversion rate
- field drop-off insights

## Shared Technical Architecture

### Registry-driven editor metadata

Both the page builder and forms builder should follow the same pattern:

- backend registry is the source of truth
- UI components are resolved from registry metadata
- schemas drive validation and inspector generation

Recommended form field registry shape:

```php
[
    'type' => 'text',
    'label' => 'Text',
    'category' => 'basic',
    'icon' => 'text',
    'defaults' => [
        'label' => 'Text field',
        'name' => 'text_field',
        'required' => false,
    ],
    'inspector' => [
        'field' => ['label', 'name', 'placeholder', 'help_text'],
        'validation' => ['required', 'minlength', 'maxlength'],
        'logic' => ['conditional_rules'],
        'appearance' => ['width', 'css_class'],
        'advanced' => ['default_value', 'admin_label'],
    ],
]
```

### Persistence

Keep current Laravel-friendly contracts:

- pages: `content_json`
- forms: structured JSON for `fields` and `settings`

### Runtime stack

- Blade shell for route mount and permissions
- Vue 3 app for editor runtime
- Pinia for state
- autosave with debounced API writes
- backend validation remains authoritative

## Delivery Plan

### Phase 1: IA and shell

- redesign page create/edit shell to WP-like two-column layout
- redesign forms builder shell to Forminator-like app layout
- do not change persisted contracts yet

### Phase 2: shared registry contracts

- formalize page block editor metadata
- formalize form field editor metadata
- expose both through API payloads

### Phase 3: production forms builder

- replace Alpine prototype with Vue builder
- add top tabs and autosave state
- add templates and better preview flow

### Phase 4: page editor refinement

- add Editor/Builder/SEO Preview modes
- add permalink editing and document rail polish
- add outline, block inserter, better preview

## Stage After This Spec

After this architecture pass, the next implementation work should be:

1. build the new page editor shell in Blade first
2. extract a dedicated Vue app for `vertex-forms`
3. move form field definitions into a real registry API
4. replace the current builder prototype incrementally instead of big-bang rewriting it

## References

- Forminator documentation, updated `May 8, 2026`: <https://wpmudev.com/docs/wpmu-dev-plugins/forminator/>
