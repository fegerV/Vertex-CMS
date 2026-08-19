# Security Reference for VertexCMS

## Purpose

The OneShot reference contains many sound security principles, but it targets CodeIgniter and project-specific helpers that VertexCMS does not use. This document translates the useful parts into Laravel conventions, identifies advice that should be modified rather than copied, and sets an implementation order for VertexCMS.

The short checklist in [`.ai/rules/security.md`](../../.ai/rules/security.md) is the mandatory review aid. This document is the rationale and adoption matrix.

## Recommendation

Adopt the reference as a **source of threat scenarios**, not as code-level policy. Roughly three groups emerge:

1. **Adopt now:** boundary validation, output encoding, CSRF, authorization/IDOR controls, mass-assignment protection, throttling, secret hygiene, upload and SSRF controls, webhook verification, atomic state changes, secure cookies/headers, dependency audits, and resource limits.
2. **Adapt to Laravel:** Blade escaping instead of `esc()`, Form Requests and policies instead of CodeIgniter controllers/filters, Eloquent/query builder instead of a mandatory model base class, Laravel rate limiters, Sanctum abilities, encrypted casts/settings, and Laravel middleware groups.
3. **Do not copy as absolutes:** hiding all integer IDs, requiring every database access to pass through a model, requiring a CSRF field in non-browser token APIs, using an obscure admin URL as a primary defense, or prescribing OneShot token/key formats.

## What VertexCMS should adopt immediately

### 1. Access control and API inventory (P0)

Authorization is the most important transferable recommendation. Every resource action must enforce both the route-level permission and object-level scope. Route model binding is lookup, not authorization; Sanctum authentication is identity, not permission.

VertexCMS should:

- require policies/gates or explicit ownership checks on `show`, `update`, `destroy`, export, download, and nested-resource actions;
- define which API routes are intentionally public and test that inventory;
- attach named throttles to public, expensive, and mutating endpoints;
- use Sanctum abilities for external tokens and permissions/policies for admin users;
- cap pagination and collection input sizes;
- return generic server errors without exception details in production.

**Repository-specific priority:** `routes/api.php` currently contains AI provider/chat routes and duplicate system info/cache-clear routes outside an authentication group. Their intended exposure must be resolved before adding more API surface. This is more urgent than introducing signed or opaque IDs.

### 2. Laravel-native input and output safety (P0)

- Validate request boundaries with Form Requests or `$request->validate()`, including scalar types for JSON input, maximum lengths, enum membership, nested array shape, and total item counts.
- Use Blade `{{ $value }}` for untrusted text. Treat `{!! $value !!}` and HTML-bearing page-builder blocks as security-sensitive sinks requiring a maintained allowlist sanitizer.
- Keep browser mutations behind Laravel's `web` middleware and `@csrf`/CSRF headers. Bearer-token APIs do not need CSRF, while cookie-authenticated endpoints do.
- Use Eloquent and the query builder with bound values. Raw queries are acceptable only when necessary, reviewed, and parameterized; user-controlled column names and sort directions require allowlists.

### 3. Mass assignment and business invariants (P0)

Every Eloquent model should use a deliberate `$fillable` or `$guarded` policy. This is only a backstop: controllers and services must explicitly derive security-sensitive values such as `user_id`, role, permissions, ownership, price, credit, publication state, and provider references from trusted server state.

Negative numbers, step skipping, duplicate redemption, client-computed totals, and unauthorized state transitions need tests even when validation and CSRF are correct.

### 4. Authentication, sessions, and brute-force resistance (P0/P1)

- Keep login messages generic and throttle by a normalized account key **and** network signal, without making shared NAT users easy to deny service.
- Regenerate the session after authentication and invalidate other sessions/reset tokens after a security-sensitive password recovery where product policy requires it.
- Store reset/verification tokens hashed, scoped to one purpose and user, expiring, and single-use.
- Keep cookies `HttpOnly`, `Secure` in HTTPS deployments, and `SameSite=Lax` or stricter. Document any `SameSite=None` exception.
- Enforce 2FA during authentication for accounts where it is enabled; protect enrollment, disable, and recovery flows with recent-password/step-up verification.
- Treat passkeys/WebAuthn as the preferred roadmap direction; TOTP and recovery codes remain useful fallback controls.

VertexCMS already has security-module plans for login hardening, password policy, sessions, 2FA, and passkeys. The OneShot recommendations reinforce that order but do not justify custom cryptography or a second authentication stack.

### 5. Uploads and stored content (P0)

- Validate MIME by inspecting content and also enforce an extension allowlist and size/dimension limits.
- Generate storage names; never concatenate client filenames into paths.
- Store uploads on a Laravel disk configured without PHP/script execution and directory listing.
- Treat SVG as active content: sanitize it rigorously, rasterize it, or disallow it. A generic `mimes:svg` rule is not enough for content served inline or same-origin.
- Authorize upload, read, update, and delete actions; do not expose private disks through predictable paths.
- Sanitize user-authored/page-builder HTML on write or render using an allowlist, and test dangerous URL schemes and event attributes.

### 6. SSRF and outbound integrations (P0)

Any preview fetcher, webhook delivery tool, import-by-URL feature, AI provider configuration check, or image downloader must:

- allow only required schemes, normally HTTPS;
- reject credentials in URLs and non-standard ports unless explicitly required;
- resolve DNS and reject loopback, private, link-local, multicast, reserved, and cloud-metadata destinations for IPv4 and IPv6;
- repeat validation after every redirect and mitigate DNS rebinding;
- set connect/total timeouts, redirect limits, download-size limits, and accepted content types;
- avoid forwarding inbound authorization/cookie headers.

A short literal blocklist (`127.0.0.1`, `10.*`) is insufficient.

### 7. Webhooks, billing, and replay safety (P0)

- Verify signatures against the exact raw body before parsing or writing data, using the provider SDK or `hash_equals()` where a custom HMAC protocol is unavoidable.
- Enforce timestamp tolerance and persist the provider event ID under a unique constraint for idempotency.
- Map provider references to local records server-side; never trust client-supplied user IDs, amounts, prices, or statuses.
- Keep a ledger append-only and make balance/quota transitions atomic.
- Encrypt provider credentials at rest, mask them in admin UI, and exclude them from serialization, logs, exceptions, jobs, and audit metadata.

These controls should be applied if/when VertexCMS billing becomes active; no OneShot-specific `chargeForce()` API should be copied.

### 8. Race conditions (P0/P1)

For balances, stock, quotas, coupons, single-use tokens, webhook events, publishing locks, and “first N” rules, use one of:

- a database transaction with `lockForUpdate()`;
- a conditional atomic update such as `UPDATE ... WHERE remaining >= ?` and verify affected rows;
- a unique database constraint with duplicate handling;
- an idempotency record created in the same transaction as the effect.

Cache locks can coordinate work but must not be the only integrity control for durable money/security state.

### 9. Secrets, logging, privacy, and incident response (P1)

- Keep secrets in environment/config or the existing encrypted settings service; never hardcode or return decrypted values.
- Establish redaction for `Authorization`, cookies, passwords, tokens, recovery codes, webhook signatures, payment payloads, and AI provider keys.
- Minimize IP, email, user-agent, prompt, and integration payload collection; define retention and deletion schedules for application and security events.
- Give each integration a separate least-privilege credential.
- Maintain a rotation runbook. Rotating `APP_KEY` is a migration because it can invalidate encrypted application data; it is not a routine first response.

### 10. Headers, TLS, CORS, and deployment (P1)

- Add centralized middleware for CSP, `X-Content-Type-Options: nosniff`, clickjacking protection (`frame-ancestors` and/or `X-Frame-Options`), `Referrer-Policy`, and a constrained `Permissions-Policy`.
- Roll out CSP in report-only mode first because the current Blade/admin frontend may contain inline scripts. Prefer nonces/hashes over permanently allowing `unsafe-inline`.
- Enable HSTS only after all required hosts are HTTPS-ready; add `includeSubDomains`/preload only after an explicit deployment review.
- Trust proxy headers only from configured proxies and generate canonical external URLs from `APP_URL`, not an arbitrary `Host` header.
- Do not combine credentialed CORS with wildcard or reflected origins. Configure exact origins only on routes that require browser cross-origin access.
- Disable debug output in production and prevent web access to `.env`, `storage`, backups, reports, quarantine, and generated internal files.

### 11. Dependencies, queues, and resource exhaustion (P1)

- Keep Composer/npm lockfiles and run `composer audit` plus `npm audit` after dependency changes and before releases.
- Review package provenance and avoid unnecessary dependencies; CI install jobs must not expose production secrets.
- Validate queued job/import payloads and never deserialize untrusted PHP objects.
- Queue scanners, integrity checks, exports, external AI work, and other expensive operations.
- Cap request bodies, uploads, archive expansion, image pixels, pagination, exports, recursion depth, regex input, and external response sizes.

## Advice to adapt or reject

| OneShot statement | VertexCMS decision | Reason |
| --- | --- | --- |
| Use `esc()` everywhere | Adapt | Blade `{{ }}` escapes by default. Audit `{!! !!}` and JavaScript/URL/attribute contexts separately. |
| Put `csrf_field()` in every POST form | Adapt | Use `@csrf` for session/browser forms and CSRF headers for same-origin AJAX. Bearer-token APIs should not use session CSRF. |
| Never expose internal IDs; always sign them | Reject as a blanket rule | IDs are identifiers, not authorization. ULIDs/signed links can reduce enumeration or support expiring actions, but policies/ownership checks prevent IDOR. |
| Database access only through models | Reject as a blanket rule | Laravel services may safely use Eloquent or the parameterized query builder. Keep persistence out of views and thin controllers, but do not ban reviewed bound queries. |
| Apply filters only to route groups, never globally | Adapt | Global middleware is correct for universal controls; auth/permission/throttle middleware should be scoped according to route intent. |
| Use OneShot `Base`, `AuthService`, `signId()`, `validateAndTrack()` | Reject implementation detail | Use Laravel models, Fortify/Sanctum-compatible auth flows, URL signatures where semantically needed, transactions, locks, and named limiters. |
| Hide `/admin` behind an obscure path | Optional defense-in-depth | It may reduce commodity noise but is not an access control. Prioritize MFA, throttling, monitoring, and authorization. |
| Encrypt secrets with AES-256-CTR | Adapt | Use Laravel encryption/encrypted casts or a reviewed KMS-backed design; do not prescribe a mode or implement custom cryptography. |
| Compare every token hash with `hash_equals()` | Adopt with nuance | Required for custom secret/MAC comparison; prefer framework/provider verification APIs and password hashing functions where applicable. |
| Add IP allowlisting to admin | Optional | Useful for controlled deployments, but unsuitable as a universal default and risky behind misconfigured proxies. |

## VertexCMS rollout order

### P0 — fix exposure and high-impact paths

1. Inventory `routes/web.php`, `routes/admin.php`, module routes, and `routes/api.php`; classify every endpoint as public, session-admin, Sanctum-user, or integration webhook.
2. Resolve unintentionally public system, cache, AI, upload, analytics, and form endpoints; add permissions and named throttles.
3. Add policy/ownership negative tests for all API resources.
4. Audit media and form uploads, especially SVG, storage execution, filenames, download authorization, and decompression/image limits.
5. Audit outbound HTTP and webhook endpoints for SSRF, signature verification, replay protection, timeout, and response limits.
6. Audit raw Blade output and page-builder HTML sanitization.

### P1 — platform hardening

1. Deliver the planned `vertex-login`, `vertex-headers`, and `vertex-password` slices.
2. Add security event redaction/retention and incident-response documentation.
3. Standardize idempotency and transaction patterns for quotas, forms, ecommerce, and background tasks.
4. Add deployment checks for production debug, HTTPS/cookies, trusted proxies/hosts, file exposure, headers, and CORS.
5. Automate dependency audits and route/security smoke checks in CI.

### P2 — advanced controls

1. Integrity baselines and queued malware scanning with audited quarantine.
2. Passkeys, session/device management, compromised-password checks with privacy-preserving caching, and API token governance.
3. CSP reporting, external alert integrations, compliance exports, and tested retention workflows.

## Review commands

The exact command set can be expanded as coverage improves:

```bash
php artisan route:list
php artisan test
composer audit
npm audit --omit=dev
rg -n '\{!!|withoutMiddleware|DB::raw|whereRaw|orderByRaw|unserialize\(|Http::|Log::|logger\(' app modules resources routes
rg -n 'Route::(post|put|patch|delete)' routes modules/*/routes
```

Grep is a prompt for manual review, not a vulnerability verdict. The long-term control is automated negative testing for authentication, authorization, validation, replay, resource limits, and concurrency.
