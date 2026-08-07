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

## Additional recommendations for a CMS

The source reference is broad, but several VertexCMS-specific trust boundaries deserve explicit controls beyond it.

### 12. Installer lifecycle (P0)

An installer is effectively a remote configuration and administrator-creation endpoint. After a successful installation:

- enforce an atomic, server-side installation lock and deny every installer route before controller logic;
- do not rely only on a client-visible flag or an environment value that a request could influence;
- prevent reinstall/reset flows from overwriting an existing database or creating a second super administrator;
- redact database credentials from validation errors, logs, flashed input, debug pages, and queued events;
- require an explicit CLI/operator recovery procedure to reopen installation.

Add tests for installed, partially installed, concurrent installation, and stale-lock scenarios.

### 13. Core updates and archive extraction (P0)

The updater can become a direct remote-code-execution supply-chain path because an accepted package writes application PHP files. VertexCMS should require:

- a signed update manifest and package digest verified against a public key pinned in the release, not a checksum delivered by the same untrusted response;
- HTTPS plus strict allowlisting of update hosts and download redirect destinations;
- archive entry validation before extraction: reject absolute paths, `../`, drive prefixes, symlinks/hardlinks, device files, excessive entry counts, and zip bombs;
- extraction into a new staging directory, validation of its manifest and required files, then an atomic deployment/swap where the hosting platform permits;
- a database migration compatibility plan, maintenance mode, preflight disk-space checks, durable backups, and a tested rollback path;
- step-up authentication and a dedicated update permission for UI-triggered updates, with a tamper-evident audit event.

Calling `ZipArchive::extractTo()` directly on a downloaded archive is not a sufficient safety boundary.

### 14. Modules, themes, and extension trust (P0/P1)

Modules and PHP themes execute with the application's privileges; they are code, not content. A module marketplace or upload flow should:

- accept only signed packages from explicitly trusted publishers, with an operator confirmation for local/unsigned development packages;
- validate manifest schema, compatible core range, declared migrations, permissions, routes, service providers, jobs, and frontend assets before activation;
- reject path traversal/symlinks and never activate code directly from a temporary upload directory;
- record install, enable, disable, upgrade, and removal events and expose the publisher/signature state in the admin UI;
- run dependency-conflict and migration preflight checks, and keep removal separate from destructive data deletion;
- document that a PHP module cannot be safely sandboxed inside the same PHP process. “Permissions” describe product capabilities, not an OS-level security boundary.

For user-editable themes, prefer constrained templates/design tokens over arbitrary PHP editing. If executable theme code is supported, restrict it to trusted administrators and treat changes like deployments.

### 15. Backups, restore, exports, and logs (P0/P1)

- Store backup archives outside the public web root on a private disk; encrypt them with a key managed separately from the archive and define retention/deletion policy.
- Require a narrow permission plus recent password/2FA confirmation for create, download, restore, and delete. Restoration should additionally require an explicit confirmation and maintenance mode.
- Prevent archive traversal, symlink restoration, overwrite outside approved roots, decompression bombs, and restoration of environment secrets into the wrong environment.
- Do not expose filesystem paths, environment content, access tokens, session payloads, or full request bodies through system-log viewers.
- Neutralize spreadsheet formulas in CSV cells beginning with `=`, `+`, `-`, `@`, tab, or carriage return; CSV escaping alone does not stop formula injection.
- Audit who created/downloaded/restored/deleted each artifact without logging its encryption key or sensitive contents.

Backups are sensitive production data, not ordinary media. A successful backup feature also requires periodic restore tests and documented recovery-time/recovery-point objectives.

### 16. Cache, publication state, and preview isolation (P0/P1)

- Include locale, site/tenant, publication state, role/ability, and other response-varying security context in cache keys.
- Never cache authenticated or permission-filtered responses in a shared public cache without an explicit safe variation strategy.
- Invalidate pages, menus, taxonomies, sitemaps, search results, API responses, and CDN entries when content is unpublished, scheduled, embargoed, deleted, or has its audience changed.
- Keep preview/draft URLs high-entropy, expiring, revocable, and scoped to one resource; add `Cache-Control: private, no-store` and `X-Robots-Tag: noindex`.
- Prevent cache poisoning through unvalidated `Host`, forwarded headers, query parameters, locale, or content-negotiation headers.
- Define a maximum scheduling clock skew and use a consistent server time source for embargoes, token expiry, and audit chronology.

### 17. PWA and service-worker boundaries (P1)

A service worker is a persistent same-origin network interceptor. VertexCMS should:

- never cache `/admin`, authenticated APIs, previews, installer responses, CSRF/session-bearing HTML, or personalized content;
- use an explicit public-asset/page allowlist rather than caching every successful GET;
- avoid caching redirects and error/authentication responses as offline content;
- version caches and delete obsolete entries on activation without deleting unrelated application caches;
- serve the worker with a narrow scope and `Service-Worker-Allowed` policy, and prevent user uploads from registering workers;
- provide a recovery path for a broken or compromised worker and test logout/offline behavior.

### 18. Email templates, redirects, and active content (P1)

- Treat email-template HTML as active content: sanitize according to an email-specific allowlist, forbid scripts/unsafe URLs, and isolate admin previews in a sandboxed iframe without same-origin privileges.
- Validate redirect destinations and imported redirect rules. Reject dangerous schemes, control whether external destinations are allowed, detect loops/chains, and prevent a broad rule from capturing admin/API/install routes.
- Protect template variables against server-side template injection: users may select documented placeholders, but must not submit arbitrary Blade/PHP expressions.
- Apply HTML sanitization consistently to page-builder previews and public rendering; a preview is still an XSS route against an administrator.

### 19. AI and retrieval features (P1)

- Treat retrieved documents, webpages, media metadata, and user prompts as untrusted data, never as system/developer instructions.
- Enforce authorization before retrieval and again before returning citations/snippets; vector similarity must not cross private content, site, locale, or tenant boundaries.
- Use tool allowlists and server-side argument validation. A model must not directly choose filesystem paths, raw SQL, recipient addresses, publication status, or billing amounts.
- Require explicit human confirmation for publish, delete, email, external request, update, and permission-changing actions.
- Minimize prompts and provider logs, redact secrets/PII, document retention and training controls, and separate provider keys by environment.
- Defend ingestion against oversized documents, decompression bombs, malicious MIME, excessive chunk counts, and repeated costly embedding jobs.

Prompt injection is not solved by input escaping; the primary controls are capability isolation, authorization, output validation, and confirmation of side effects.

### 20. Security assurance and operations (P1/P2)

- Add route-inventory tests that fail when a new sensitive endpoint lacks the expected auth, permission, and throttle middleware.
- Maintain reusable authorization contract tests for every resource: guest denied, wrong user/role denied, correct principal allowed.
- Add tests for webhook replay, concurrent redemption/quota use, archive traversal, CSV injection, stored XSS, SSRF redirects/IPv6, and cache visibility changes.
- Generate an SBOM for releases, sign release artifacts, protect CI provenance, pin third-party actions, and separate build credentials from deployment credentials.
- Centralize security telemetry with stable event IDs, severity, actor/target, request correlation, redaction, retention, and alert thresholds. Protect logs from modification and restrict export access.
- Define incident playbooks for account compromise, leaked provider key, malicious module/update, exposed backup, content defacement, and service-worker compromise; rehearse restoration and credential rotation.
- Run threat modeling for new modules before implementation, especially ecommerce, forms, AI tools, import/export, updater, and multi-site/headless features.

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
7. Lock the installer after first use and test concurrent/partial installation states.
8. Require signed update artifacts and safe staged archive extraction before enabling UI-driven core/module updates.
9. Move backups/exports to private storage and protect download/restore/delete with step-up authentication.
10. Verify that drafts, previews, unpublished content, admin responses, and authenticated APIs cannot enter shared or service-worker caches.

### P1 — platform hardening

1. Deliver the planned `vertex-login`, `vertex-headers`, and `vertex-password` slices.
2. Add security event redaction/retention and incident-response documentation.
3. Standardize idempotency and transaction patterns for quotas, forms, ecommerce, and background tasks.
4. Add deployment checks for production debug, HTTPS/cookies, trusted proxies/hosts, file exposure, headers, and CORS.
5. Automate dependency audits and route/security smoke checks in CI.
6. Harden CSV exports, email-template previews, redirects, PWA cache policy, and AI/RAG capability boundaries.

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
