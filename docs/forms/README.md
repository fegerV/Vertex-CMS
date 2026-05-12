# Vertex Forms Module

Advanced form builder for VertexCMS with calculator, conditional logic, multi-page, file uploads, analytics, import/export.

## Architecture

- **Schema-based**: All forms stored as validated JSON
- **Module structure**: ertex-forms following modular monolith pattern
- **Backend**: Laravel 11+ with service container, events, queues
- **Frontend**: Alpine.js (public) + Vue 3 + Inertia (admin builder - coming soon)
- **Database**: MySQL/PostgreSQL/SQLite

## Features

- 40+ field types
- Conditional logic
- Live calculator with safe formula parser
- Multi-page forms with progress indicator
- File uploads with MIME validation
- Form versioning (undo/redo)
- Analytics dashboard
- Import/Export JSON
- Anti-spam: honeypot, reCAPTCHA, Turnstile
- Rate limiting
- Email notifications
- Progressive enhancement
- Full accessibility

## Installation

Module is installed by default.

Publish config: php artisan vendor:publish --provider="Vertex\Forms\VertexFormsServiceProvider" --tag=config

## Database Tables

- orms – form definitions
- orm_fields – field definitions
- orm_submissions – submissions
- orm_submission_values – values
- orm_versions – snapshots
- orm_analytics – daily stats

## API Reference

See inline documentation in controllers.

## Services

- FormService – core logic
- FormCalculatorEngine – safe math parser
- FormConditionEngine – conditional evaluation
- FormImportExportService – JSON serialization
- FormAnalyticsService – stats aggregation

## License

MIT
