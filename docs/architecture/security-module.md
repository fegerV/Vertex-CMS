# Vertex Security Module

## Goal

`vertex-security` is the unified security module for VertexCMS. It replaces the WordPress-style approach of stacking unrelated plugins with a single module that exposes pluggable security submodules, a shared rule pipeline, and one admin control surface.

This aligns with the product direction in `v0.7`: security should be built into the platform, not assembled ad hoc per site.

## Why a Unified Module

- Removes overlapping middleware and duplicate DB writes.
- Centralizes IP, user, session, and audit context.
- Avoids conflicting settings across isolated plugins.
- Gives administrators one dashboard for posture, incidents, and tuning.
- Makes shared-hosting fallbacks a first-class concern instead of an afterthought.

## Scope

The module is split into these submodules:

- `vertex-login`: login hardening, 2FA, passkeys, session/device management, admin path hardening.
- `vertex-waf`: rate limiting, IP rules, bot filtering, Geo/IP controls, Cloudflare sync.
- `vertex-headers`: CSP, HSTS, X-Frame-Options, Referrer-Policy, Permissions-Policy, report endpoints.
- `vertex-audit`: normalized security and admin activity logging with retention and export.
- `vertex-integrity`: file baseline, hash comparison, drift detection, restore workflow.
- `vertex-password`: password policy, expiry, history, compromised-password checks.
- `vertex-scanner`: heuristic malware scanning, quarantine, reporting.
- `vertex-api-sec`: Sanctum hardening, token controls, scope rules, route exposure toggles.

## Architecture Principles

### Shared pipeline

Requests should pass through a deterministic security pipeline before business routes execute:

1. Request fingerprinting.
2. IP and user reputation checks.
3. Rate limiting and temporary bans.
4. Bot and challenge checks.
5. Header policy injection.
6. Audit event dispatch.

Each stage must be independently toggleable and must fail closed only when explicitly configured to do so.

### Event-driven internals

Security actions should emit Laravel events instead of relying on global hooks:

- `LoginAttackDetected`
- `SecurityRuleTriggered`
- `IntegrityDriftDetected`
- `PasswordCompromisedDetected`
- `ScannerThreatDetected`

This keeps the module testable and allows future modules to subscribe without tight coupling.

### Shared data contracts

Submodules should share normalized DTO-style payloads for:

- actor context
- network context
- device/session context
- threat verdicts
- remediation actions

The main value here is consistency: `vertex-waf`, `vertex-login`, and `vertex-audit` should not each invent separate formats for IP, user agent, or incident severity.

### Queue-first heavy work

Anything that touches many files, external APIs, or large exports must be queued:

- integrity scans
- malware scans
- HIBP checks with cache miss
- large audit exports
- Cloudflare synchronization

The public request path should only enqueue jobs and write a compact audit trail.

## Storage Model

### Primary tables

- `security_events`
- `security_rules`
- `security_ip_rules`
- `security_user_blocks`
- `security_sessions`
- `security_passkeys`
- `security_two_factor_recoveries`
- `security_password_history`
- `security_scan_results`
- `security_quarantine_files`

### File storage

- `storage/security/baseline.json`
- `storage/security/reports/*.json`
- `storage/app/quarantine/*`

### Reuse of existing core data

VertexCMS already has activity logging, settings, Sanctum, and base rate limiting. `vertex-security` should reuse and extend those capabilities instead of duplicating them.

## Integration With Existing VertexCMS

Current repository foundations already map well to this module:

- `App\System\Services\ActivityLogService` can remain the generic app log service.
- `vertex-audit` should build a security-oriented layer on top of it or alongside it for richer retention/export semantics.
- `App\Providers\AppServiceProvider` already defines basic rate limiters; `vertex-waf` should eventually own named policies and fallback behavior.
- `laravel/sanctum` is already present in `composer.json`, which lowers the implementation cost for `vertex-api-sec`.
- The existing `modules/vertex-forms` structure provides the module skeleton pattern to follow.

## Shared-Hosting Fallback Strategy

The module must not assume Redis, Horizon, shell access, or external daemons.

### Preferred mode

- Redis-backed throttles and queues
- MaxMind GeoIP database
- WebSockets/Reverb alerts
- Cloudflare API sync

### Fallback mode

- file/database cache
- database queue or cron-driven processing
- local JSON rotation for audit exports
- TOTP-only auth when WebAuthn is unavailable
- static header presets when dynamic CSP reporting is not viable

Fallback mode is part of the design, not a degraded afterthought.

## Admin UX

The admin surface should appear as one Security section with tabs or cards for:

- Overview
- Login & Sessions
- Firewall & Rules
- Headers
- Audit
- Integrity
- Password Policy
- Scanner
- API Security

The dashboard should surface:

- current risk posture
- recent incidents
- blocked IPs/users
- scan status
- integrity drift status
- header preset status
- passkey and 2FA adoption

## Delivery Plan

Recommended order:

1. `vertex-login`
2. `vertex-headers`
3. `vertex-password`
4. `vertex-waf`
5. `vertex-audit`
6. `vertex-integrity`
7. `vertex-api-sec`
8. `vertex-scanner`

This order keeps early releases focused on hardening the most exposed surfaces first.

## Non-Goals For The First Slice

- full malware signature parity with enterprise scanners
- country-level blocking that depends on paid services
- mandatory real-time websockets
- automatic file restore without explicit operator confirmation
- deep edge-WAF replacement for Cloudflare or similar providers

## Current Status In This Repo

This repository now contains a `vertex-security` module skeleton and planning docs, but the module is not yet wired into Composer autoloading or application providers in this environment.

That final integration should happen on a machine where `composer` and `php artisan test` are available.
