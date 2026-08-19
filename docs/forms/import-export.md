# Import / Export

Full JSON round-trip for form definitions.

## Export

### API

GET /admin/forms/{form}/export-json

Returns pplication/json with all form data including fields.

Response:

`json
{
  "form": {
    "id": 1,
    "name": "Contact",
    "slug": "contact",
    "type": "standard",
    "description": "...",
    "settings": { ... },
    "fields": [
      {
        "id": 1,
        "name": "name",
        "label": "Name",
        "type": "text",
        "required": true,
        "options": { "maxlength": 255 },
        ...
      }
    ]
  }
}
`

Save as orm-{slug}-{timestamp}.json.

### Via Service

`php
 = ->export();
 = json_encode(, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
`

## Import

### API

POST /admin/forms/{form}/import-json (or POST /admin/forms/import for new)

Body: multipart/form-data with ile (JSON file).

Process:
1. Validate JSON structure
2. Validate each field against FieldTypeRegistry schema
3. Check required props: 
ame, label, 	ype
4. Create new Form + FormField records (bulk insert)
5. Return created form ID + redirect URL

### Validation Errors

If import fails, returns:

`json
{
  "success": false,
  "errors": [
    "Field 'email_2' missing required property 'label'",
    "Field 'price' has invalid type 'currenc'",
    "Duplicate field name: 'name'"
  ]
}
`

### Duplicate Forms

Import creates a new form (different ID). To replace existing, use "Overwrite" option (future).

## Programmatic Import

`php
 = json_decode(, true);
 = ->import();
`

## Schema Rules

- orm.name – string, unique, alpha_dash
- orm.slug – string, unique, alpha_dash
- orm.type – in: standard,calculator,survey,poll
- ields[].type – must exist in FieldTypeRegistry::SCHEMAS
- ields[].name – required, matches /^[a-z_][a-z0-9_]*$/i
- ields[].label – required, string
- ields[].options – JSON object, validated per field type's prop schema

## Round-Trip Guarantee

Exported JSON can be re-imported without data loss. All fields kept:

- id → ignored on import (new IDs generated)
- created_at, updated_at → set to now
- orm_id on fields → auto-assigned to new form
- sort_order preserved

## Version Compatibility

- Forms exported from v1.0 can be imported into v1.1+ (forward compatible)
- New field types added in future will be imported with 	ype: "unknown" with fallback to text renderer
- Unknown options are preserved but ignored

## Bulk Operations

Multiple forms can be exported as ZIP archive (future).

## Security

- Import requires orms.create permission
- Max file size: 5MB (configurable)
- Uploaded files scanned for PHP tags (blocked)
- CSRF-protected endpoint

## Best Practices

1. Always backup current form before import
2. Test import in development/staging first
3. Keep exported JSON in version control (Git)
4. Use descriptive form names and slugs
5. Validate formulas and conditions after import (re-calc)
