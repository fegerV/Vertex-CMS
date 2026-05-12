# Vertex Security Implementation Plan

## Objective

Ship `vertex-security` as a modular security foundation for VertexCMS without destabilizing the current core and forms work already in progress.

## Phase 1

Focus:

- `vertex-login`
- `vertex-headers`
- `vertex-password`

Deliverables:

- login rate limiting and lockout policy
- optional hidden admin path
- TOTP enrollment and recovery codes
- initial session/device inventory
- security header presets: `strict`, `relaxed`, `custom`
- password complexity, history, and expiry foundation

Acceptance:

- admin login brute-force protection works with cache fallback
- header middleware can be turned on without editing templates
- password policy is enforced on reset and password change flows

## Phase 2

Focus:

- `vertex-waf`
- `vertex-audit`

Deliverables:

- IP allow/deny rules
- named rule storage for bot and request filtering
- Cloudflare sync abstraction
- dedicated security event model and export format
- retention scheduler

Acceptance:

- blocked IPs are enforced before admin/business controllers
- audit retention can be configured without code edits
- security events export to JSON/CSV

## Phase 3

Focus:

- `vertex-integrity`
- `vertex-api-sec`

Deliverables:

- baseline command
- queued drift scan
- Sanctum token hardening policies
- API route exposure toggles
- scope-aware token dashboard contract

Acceptance:

- baseline file can be created and compared without shell access
- API security can be tightened independently of the public site

## Phase 4

Focus:

- `vertex-scanner`
- external integrations

Deliverables:

- heuristic scanner
- quarantine disk
- HIBP cache-backed breach checks
- Cloudflare synchronization jobs

Acceptance:

- scanner runs off the request thread
- quarantine actions leave a full audit trail

## Phase 5

Focus:

- unified Security Dashboard
- live alerts
- compliance-oriented reporting

Deliverables:

- posture overview widgets
- incident activity feed
- configuration warnings
- exportable reports for retention and access review

Acceptance:

- admin can evaluate all enabled security submodules from one screen
- dashboard degrades gracefully when websockets are unavailable

## Integration Notes

- Do not duplicate generic app activity logs blindly; security events need their own retention and severity semantics.
- Reuse existing Laravel rate limiter entry points where possible.
- Keep all expensive scans queued.
- Treat Redis as optional, not mandatory.
- Avoid editing unrelated dirty files until runtime integration is ready.

## Verification Checklist

- no feature requires `exec()` or `shell_exec()`
- all critical middleware can run with file/database cache
- default configuration remains safe on shared hosting
- secrets never appear in logs, exports, or frontend payloads
- jobs can downgrade from Redis/Horizon to database queue/cron
- dashboard clearly shows disabled or degraded submodules
