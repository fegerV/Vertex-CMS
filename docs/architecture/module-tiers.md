# Module Tiers

## Goal

VertexCMS should separate extensibility into three layers:

1. Core modules
2. Builtin optional modules
3. Marketplace plugins

This keeps Laravel-style monolith performance for the default product while still allowing WordPress-like extensibility where it is actually useful.

## Tiers

### Core

Core modules are always active and versioned with the product. They are not toggleable from the admin UI.

Examples:

- `vertex-core`
- `vertex-auth`
- `vertex-content`
- `vertex-media`
- `vertex-seo`
- `vertex-themes`
- `vertex-settings`
- `vertex-installer`

Rules:

- loaded at bootstrap
- migrations ship with the main product
- may expose contracts consumed by lower tiers
- must be stabilized before dependent modules are published

### Builtin

Builtin modules ship with VertexCMS but can be enabled or disabled in the admin UI.

Examples:

- `vertex-forms`
- `vertex-orders`
- `vertex-i18n`
- `vertex-cache`
- `vertex-analytics`
- `vertex-backups`
- `vertex-security`

Rules:

- packaged inside the repository under `modules/*`
- discovered via `module.json`
- should use their own tables or pivot tables
- should integrate with core only through contracts and events

### Marketplace

Marketplace plugins are installed separately and should be treated as external packages with richer lifecycle controls.

Examples:

- `vertex-workflow`
- `vertex-api`
- `vertex-search`
- `vertex-ai`
- `vertex-git-sync`
- `vertex-marketplace`

Rules:

- not assumed to exist at runtime
- must declare dependencies explicitly
- should be sandboxed by installation and activation state
- should not block core boot if missing

## Module Contract

Each module should expose metadata through `module.json`.

Minimum recommended fields:

```json
{
  "module": {
    "id": "vertex-forms",
    "name": "Forms",
    "tier": "builtin",
    "activation": "admin_toggle",
    "version": "1.0.0",
    "providers": [
      "Vertex\\Forms\\VertexFormsServiceProvider"
    ],
    "dependencies": {
      "vertex-core": "^0.1"
    }
  }
}
```

## Repository Layout

### Core

Core modules primarily live under `app/` and `config/`.

### Builtin

Builtin modules live under `modules/<module-id>/`.

Recommended shape:

```text
modules/vertex-seo/
├── src/
│   ├── Controllers/
│   ├── Models/
│   ├── Events/
│   ├── Listeners/
│   ├── Contracts/
│   └── Services/
├── database/migrations/
├── resources/
│   ├── views/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
├── config/seo.php
├── module.json
└── VertexSeoServiceProvider.php
```

### Marketplace

Marketplace plugins may live outside the repository root and should be resolved by an installer/package manager flow later.

## Runtime Design

The product should use a module manifest loader that:

- seeds always-on core modules from config
- scans `modules/*/module.json` for builtin modules
- later accepts marketplace entries from an installed package registry

The loader should not hardcode each module in routes or providers forever.

## Current Repo Direction

This repository now has:

- `config/modules.php`
- `App\Modules\Support\ModuleManifest`
- `App\Modules\Support\ModuleManifestLoader`
- `App\Modules\Support\ModuleCatalog`
- `App\Modules\Services\ModuleManager`

These classes establish the tier model and manifest format without forcing full runtime activation yet.

## Next Step

After the existing module autoload issue is resolved, wire the module manager into bootstrap so provider registration, admin toggles, and route loading can be driven by manifest data instead of manual includes.
