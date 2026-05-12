# Public API Reference

Endpoints for rendering and submitting forms on the frontend.

## Base URL

All public form routes are under /forms prefix.

## Configuration Endpoint

### Get Form Config

GET /forms/{form:slug}

Returns JSON configuration for the form SPA.

**Response:**

`json
{
  "form": {
    "id": 1,
    "name": "Contact Us",
    "description": "Get in touch",
    "settings": {
      "theme": "default",
      "button_text": "Send Message",
      "success_message": "Thanks! We'll reply soon.",
      "enable_honeypot": true,
      "enable_recaptcha": false,
      "multipage": false
    }
  },
  "fields": [
    {
      "id": 1,
      "name": "name",
      "label": "Your Name",
      "type": "text",
      "required": true,
      "visible": true,
      "position": 1,
      "options": { "maxlength": 255 },
      "placeholder": "John Doe",
      "help_text": "Please enter your full name"
    }
  ],
  "current_page": 1,
  "total_pages": 1,
  "show_progress": false
}
`

**Rendering:** Frontend (Alpine.js) consumes this and builds the form dynamically.

**Caching:** Response cached for 5 minutes (Cache-Control: public, max-age=300).

## Submission Endpoint

### Submit Form

POST /forms/{form:slug}/submit

Content-Type: multipart/form-data (for file uploads) or pplication/x-www-form-urlencoded.

**Request Body:**

All form fields as regular POST parameters. For file fields, use multipart/form-data and attach files.

Example:
`ash
curl -X POST https://example.com/forms/contact \
  -F "name=John Doe" \
  -F "email=john@example.com" \
  -F "message=Hello"
`

**Success Response (200):**

`json
{
  "success": true,
  "message": "Форма успешно отправлена!",
  "submission_id": "a1b2-c3d4-e5f6"
}
`

**Validation Error (422):**

`json
{
  "success": false,
  "message": "Please fix the errors below.",
  "errors": {
    "email": ["The email field is required.", "The email must be valid."],
    "message": ["The message must be at least 10 characters."]
  }
}
`

**Server Error (500):**

`json
{
  "success": false,
  "message": "Something went wrong. Please try again later."
}
`

**Anti-spam rejection (403):**

`json
{
  "success": false,
  "message": "Spam detected.",
  "errors": { "_honeypot": ["Honeypot triggered"] }
}
`

### Rate Limiting

Per IP: config('forms.max_submissions_per_minute', 10)

Returns 429 Too Many Requests if exceeded.

## Headers

All responses include:

- X-Form-ID: {id}
- X-Submission-ID (on success)

## CORS

Public API allows cross-origin requests (if needed). Configured in cors.php:

`php
'paths' => ['forms/*'],
'allowed_methods' => ['GET', 'POST'],
'allowed_origins' => ['*'], // or specific domains
`

## Security

- CSRF token included as hidden _token field (verified by Laravel middleware)
- reCAPTCHA v3 token validated if enabled
- Turnstile token validated if enabled
- Rate limited by IP
- SQL injection prevented (Eloquent)
- XSS prevented (escaped output)

## JavaScript Usage Example

`javascript
async function submitForm(formId, formData) {
    const form = document.getElementById(formId);
    const data = new FormData(form);
    
    try {
        const response = await fetch(/forms//submit, {
            method: 'POST',
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(result.message);
            console.log('Submission ID:', result.submission_id);
        } else {
            showErrors(result.errors);
        }
    } catch (error) {
        console.error('Submit failed:', error);
    }
}
`

## Progressive Enhancement

If JavaScript disabled:
- Form posts to same URL (/forms/{slug}) with normal POST
- Server validates and redirects back with errors in session
- Success message shown via flash

The Blade view locks/form.blade.php includes a <noscript> fallback message.

## Webhooks (Future)

POST /forms/{slug}/webhook – push submission data to external URL (configured in form settings). Payload matches submission JSON.

## Export Formats

- Default: JSON
- CSV (via admin only)

## Versioning

Public API version is implicit in URL structure. Future v2 API might be under /api/v2/forms/... with different schemas.

## Error Codes

| Code | Meaning | Action |
|------|---------|--------|
| 200 | Success | Show success message |
| 400 | Bad request (invalid JSON) | Check request format |
| 401 | Not authenticated | N/A (public) |
| 403 | Forbidden (spam, rate limit) | Wait or solve CAPTCHA |
| 404 | Form not found | Check slug |
| 422 | Validation failed | Display errors |
| 429 | Too many requests | Slow down, wait 60s |
| 500 | Server error | Contact admin |

## Testing the API

### Using cURL

`ash
# Get config
curl https://example.com/forms/contact

# Submit with file
curl -X POST https://example.com/forms/contact \
  -F "name=John" \
  -F "email=john@example.com" \
  -F "resume=@/path/to/cv.pdf"
`

### Using Postman

Import collection: docs/api/forms-postman-collection.json (future).

## SDKs (future)

- PHP SDK: ertexcms/forms-php
- JavaScript SDK: @vertexcms/forms-js

## Changelog

- v1.0 – initial public API
