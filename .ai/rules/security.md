# VertexCMS Security Checklist

Read this checklist for every code change. Use the detailed rationale and module-specific guidance in [`docs/security/reference.md`](../../docs/security/reference.md) when the change touches authentication, APIs, uploads, outbound HTTP, secrets, billing, queues, or infrastructure.

## Every change

- Validate and normalize data at trust boundaries with Laravel Form Requests or `validate()`; include type, length, collection-size, and pagination limits.
- Authorize every object-level action with policies, gates, or explicit ownership checks. Authentication and unguessable IDs do not replace authorization.
- Keep privileged attributes out of validated input and Eloquent `$fillable`; prefer explicit assignments for roles, permissions, ownership, prices, and status transitions.
- Render untrusted text with Blade `{{ }}`. Use `{!! !!}` only for content that has passed an allowlist HTML sanitizer.
- Keep state-changing browser routes in the `web` middleware group and send CSRF tokens from forms/AJAX. Do not disable CSRF to fix a client integration.
- Use Eloquent/query-builder bindings. Never interpolate request data into SQL, column names, `orderByRaw()`, or raw expressions.
- Keep secrets in environment/config or encrypted settings. Never put passwords, tokens, authorization headers, webhook bodies, or provider secrets in logs, events, exports, URLs, or frontend payloads.
- Apply named rate limiters to login, reset, verification, public submissions, expensive AI/search endpoints, uploads, webhooks, and API mutations.
- Review every new route's middleware, permission, and public/private intent. Public exceptions must be explicit and tested.

## High-risk changes

- **Uploads:** validate content and extension, cap size, generate the stored name, use a non-executable disk, and authorize reads/deletes.
- **Outbound URLs:** allow only required schemes, resolve and reject loopback/private/link-local/reserved addresses (including every redirect and both IP families), set timeouts and response-size limits.
- **Webhooks:** verify the provider signature on the raw body before side effects; enforce timestamp tolerance and event-ID idempotency.
- **Money, quotas, and one-time tokens:** use a transaction plus row lock, a conditional atomic update, or a unique constraint. Never use a PHP read-check-write sequence.
- **Redirects and absolute URLs:** accept only local destinations and generate external links from configured `APP_URL`, not the request `Host` header.
- **Queues/imports:** validate payload shape when consuming; never `unserialize()` or execute payload content.
- **Updates/modules/themes:** verify package provenance and signatures, reject archive path traversal and symlinks, stage changes, and support a tested rollback.
- **Backups/exports:** require narrow permissions and recent-auth confirmation, encrypt sensitive archives, prevent CSV formula injection, and never place artifacts on a public disk.
- **Caches/service workers:** include authorization and tenant scope in cache keys, invalidate on visibility changes, and never offline-cache admin or authenticated API responses.

## Before review

- Run focused tests, `php artisan route:list`, `composer audit`, and `npm audit --omit=dev` where available.
- Inspect the diff for raw Blade output, CSRF exclusions, mass assignment, public routes, raw SQL, insecure redirects, and sensitive logging.
- Add negative tests for unauthenticated, unauthorized, invalid, replayed, oversized, and concurrent requests as applicable.
