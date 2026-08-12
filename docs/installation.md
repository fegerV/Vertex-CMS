# Installation and configuration

## Requirements

VertexCMS 1.0 requires PHP 8.2+, PDO/MySQL, mbstring, OpenSSL, fileinfo, tokenizer, XML, ctype, JSON, bcmath, cURL, ZIP, and GD or Imagick. `storage`, `bootstrap/cache`, and `public/uploads` must be writable.

## Fresh installation

1. Configure the web root to `public/` and install Composer and frontend dependencies.
2. Open `/install`; resolve every failed requirement before continuing.
3. Supply an HTTPS site URL, locale/timezone, MySQL connection, and the first administrator credentials.
4. The installer checks the connection, writes environment values, generates the app key, runs all migrations/seeders, creates the administrator/home page, then writes `storage/app/installed.lock`.
5. Remove write access to `.env` where the host supports it and sign in at `/admin`.

The lock prevents reinstallation. Do not delete it on an active site. Installer failures return a generic response; diagnostic details belong in server logs so credentials are not disclosed.

## Configuration and transfer

Runtime configuration belongs in `.env`; site configuration belongs in the settings repository. Portable settings exports are versioned JSON. By default they omit API keys, SMTP passwords, bot tokens, and other catalogued secrets. Review an export before moving it between environments. Import applies known keys transactionally and invalidates the settings cache.

## Operations

Run `php artisan migrate --force` during an upgrade and `php artisan optimize:clear` after deployment. Configure scheduled database/files backups and retention. Test restore procedures on a non-production environment; having backup files without a tested restore is not a recovery plan.
