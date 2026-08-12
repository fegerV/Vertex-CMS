# Upgrade guide

## Supported path

Minor and patch upgrades within VertexCMS 1.x are in-place and require no manual database edits. Always upgrade one minor series at a time when a release note says so. A major upgrade may require a documented migration path.

## Procedure

1. Read all intervening release notes and verify PHP/module compatibility.
2. Put the site in maintenance mode and stop queue workers.
3. Create and verify database and files backups. Registered backup hooks can coordinate external snapshots.
4. Deploy code and locked dependencies.
5. Run `php artisan migrate --force`; never edit schema manually.
6. Run `php artisan optimize:clear`, restart workers, and execute health/smoke checks for login, pages, media, API v1 and scheduled jobs.
7. Leave maintenance mode only after checks pass.

## Compatibility promises

`/api/v1` fields are not removed, retyped, or reinterpreted in 1.x patch/minor releases. Additive fields are permitted. Public contracts and events follow the same rule. Database migrations are additive/reversible where safe; destructive changes require a major release and explicit release note.

## Rollback

Application code may be rolled back only when the release notes say its migrations are backward compatible. Otherwise restore the pre-upgrade database/files snapshot. Record failures and hook output before restoring.
