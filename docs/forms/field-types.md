# Field Types Reference

Complete reference for all form field types available in Vertex Forms.

## Core Field Types

### Text Input (	ext)

Single-line text field.

**Properties:**
- 
ame (required) – field ID
- label (required) – display label
- placeholder
- maxlength (default: 255)
- minlength
- default_value
- equired (boolean)

**Validation:** string, max:255, min:?

**Frontend:** TextRenderer.vue

**Example:**
`json
{
  "type": "text",
  "name": "first_name",
  "label": "First Name",
  "required": true,
  "options": { "maxlength": 100 }
}
`

---

### Email (email)

Email input with built-in browser validation.

**Properties:**
- 
ame, label, placeholder, equired
- default_value

**Validation:** equired, email

**Frontend:** TextRenderer.vue (type="email")

---

### Phone (	el)

Telephone number input.

**Properties:** same as text, plus:
- pattern – regex for formatting

**Validation:** string, regex pattern if provided

---

### Number (
umber)

Numeric input with spinner.

**Properties:**
- min
- max
- step (default: 1)
- default_value (numeric)

**Validation:** 
umeric, min:?, max:?

---

### Textarea (	extarea)

Multi-line text input.

**Properties:**
- ows (default: 4)
- cols
- placeholder
- equired

**Validation:** string, max:? (via options)

---

### Rich Text / HTML (html)

Static HTML content block.

**Properties:**
- content – raw HTML

**Validation:** None (admin only)

**Use:** Add formatted text, embedded videos, iframes.

---

### Heading (heading)

Section heading (h1-h6).

**Properties:**
- 	ext – heading text
- level – h1, h2, h3, h4, h5, h6 (default: h2)

---

### Divider (divider)

Horizontal rule with optional label.

**Properties:**
- label – text shown on divider line
- style – solid, dashed, dotted

---

### Select Dropdown (select)

Single-select dropdown.

**Properties:**
- options – { "value1": "Label 1", "value2": "Label 2", ... }
- placeholder – first empty option
- equired

**Frontend:** native <select>

**Validation:** in:value1,value2,...

---

### Radio Buttons (adio)

Radio group (single choice).

**Properties:**
- options (same format)
- inline (boolean) – display horizontally

**Frontend:** Radio inputs wrapped in labels

---

### Checkbox (checkbox)

Single checkbox (true/false).

**Properties:**
- label – text next to checkbox
- equired

Use for terms agreement, newsletter signup.

---

### Checkbox Group (checkbox_group)

Multiple checkboxes (array of values).

**Properties:**
- options
- equired – at least one must be checked

---

### Multi-Select (multiselect)

Multi-select dropdown (Ctrl+click or JS enhanced).

**Properties:**
- options
- max_selections

---

### Date (date), Time (	ime), DateTime (datetime-local)

Native HTML5 date/time pickers.

**Properties:**
- min, max
- ormat (display format for validation)

---

### File Upload (ile)

File upload with validation.

**Properties:**
- llowed_types – array of MIME types or extensions
- max_size – in MB
- multiple – allow multiple files
- upload_dir – subdirectory under storage/app/public/form-uploads/{form_id}/
- ename – boolean; if true, files renamed to UUID

**Security:**
- MIME type sniffing (finfo)
- Extension whitelist
- Max size enforcement
- Optional virus scan (ClamAV - future)

**Storage:** storage/app/public/form-uploads/ → symlinked to public/storage

**Frontend:** <input type="file"> with preview (images)

**Validation:** ile, mimes:jpeg,png,pdf,..., max:?

---

### Hidden Field (hidden)

Hidden input (not visible to user).

**Properties:**
- default_value – static value or {dynamic} placeholder

**Use cases:** Store form ID, user ID (if logged in), UTM parameters, referrer.

---

### Calculator (calculator)

Live calculated field (read-only).

**Properties:**
- ormula – math expression with field placeholders: {quantity} * {price} - {discount}
- depends_on – array of field names (auto-populated)
- prefix – e.g., $, €
- suffix – e.g., %,  kg
- precision – decimal places (default: 2)
- default_value – initial value before calculations

**Engine:** FormCalculatorEngine (shunting-yard parser)

**Frontend:** Real-time Alpine.js reactive watcher

---

### Page Break (page_break)

Divides form into multiple pages.

**Properties:**
- 	itle – optional page title (shown in progress)
- description – optional page description

**Behavior:** When encountered during rendering, all subsequent fields go to next page.

---

### Section (section)

Visual grouping container (not a field, but a layout element).

**Properties:**
- 	itle – section heading
- description – subtext
- collapsible – allow collapse/expand
- default_open – true/false

**Future:** Will wrap fields in a fieldset.

---

### Repeater (epeater)

Allows dynamic addition/removal of field groups.

**Properties:**
- ields – nested field definitions
- min – minimum repeats
- max – maximum repeats
- dd_button_label
- emove_button_label

**Frontend:** Vue dynamic component (future). Currently not implemented.

**Use case:** Order forms (multiple items), list of attendees, etc.

---

## Conditional Logic per Field

Every field supports a conditional object:

`json
"conditional": {
  "depends_on": "field_name",
  "operator": "equals",
  "value": "yes"
}
`

**Operators:**
- equals – exact match
- 
ot_equals – not equal
- contains – substring contains
- greater_than – numeric comparison
- less_than – numeric comparison
- is_empty – show when field is empty
- is_not_empty – show when field has value

**Multiple Conditions (future):**
`json
"conditional": {
  "any": [
    { "depends_on": "country", "operator": "equals", "value": "US" },
    { "depends_on": "country", "operator": "equals", "value": "CA" }
  ]
}
`

---

## Calculated Fields

Dependencies auto-detected from formula:

Formula: {price} * {quantity} * (1 - {discount_rate}/100)

Dependencies: price, quantity, discount_rate

All dependency fields must be numeric (int/float/string numeric).

Real-time: As user types, calculator updates instantly.

Precision: 2 decimal places by default; can be changed in field props.

---

## Validation Rules

Each field type has default Laravel validation rules. Custom rules can be added via options.validation array.

Common validations:
- equired – field must not be empty
- string – must be string
- 
umeric – must be number
- integer – whole number
- min:value, max:value
- email – valid email
- url – valid URL
- ile – must be uploaded file
- mimes:jpg,png,pdf – allowed extensions
- max:2048 – file size in KB

**Conditional required:** Use equired_if:other_field,value (planned).

---

## Styling & CSS

Each field supports css_class property for custom Tailwind classes:

`json
{
  "css_class": "col-span-2 bg-gray-50"
}
`

Themes:
- default – standard Vertex styling
- minimal – borders only on focus
- card – each field in separate card

Global CSS can target:
- .vc-form-wrapper – container
- .vc-form-field – individual field wrapper
- .vc-field-{type} – type-specific styling (e.g., .vc-field-text)

---

## Field Import/Export

When exporting a form, each field includes:

`json
{
  "id": 12,
  "name": "email",
  "label": "Email Address",
  "type": "email",
  "sort_order": 2,
  "options": {},
  "required": true,
  "visible": true,
  "default_value": "",
  "placeholder": "you@example.com",
  "help_text": "We'll never share your email.",
  "css_class": "",
  "conditional": null,
  "calculator": null
}
`

When importing, FieldTypeRegistry validates the schema. Missing required props (
ame, label, 	ype) will cause import failure with error details.

---

## Limitations & Gotchas

- Field 
ame must be unique within a form (used as array key in submission)
- Field 
ame cannot contain spaces or special chars (only -z0-9_)
- Calculator field dependencies must exist and be numeric; missing dependencies show as 0
- Conditional logic only evaluates on frontend; backend re-evaluates on submit for security
- File uploads: total request size limited by PHP_MAX_POST_SIZE and upload_max_filesize
- Multi-page: page_break cannot be first field; at least one field needed per page
- Importing a form with duplicate slug will auto-rename slug with timestamp

## Future Field Types

- Signature pad
- reCAPTCHA invisible
- Rating (stars)
- Range slider
- Color picker
- URL with preview
- Password strength meter
- Address (street, city, zip, country)
- Name (first + last combined)
- Toggle switch (better than checkbox)
- Rating (stars/emoji)
- Matrix (grid of checkboxes/radios)
