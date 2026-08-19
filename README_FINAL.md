# VertexCMS - Status Summary

## Date

`2026-05-07`

## Current Status

Статус проекта: `active development`.

Текущая документационная оценка:

- P0 реализован в коде.
- P0 не подтверждён end-to-end в runtime.
- `robots.txt` пока остаётся статическим, поэтому часть P0 про data-driven SEO-инфраструктуру ещё не идеальна.

## Что реализовано

### Core CMS

- Installer backend.
- Auth foundation.
- Admin layout.
- RBAC, users, roles, permissions.
- Settings UI и public settings API.
- Activity logs и system info.
- Cache clear UI.

### Pages

- Pages CRUD.
- Slug generation и validation.
- URI generation и уникальность.
- Draft/published/scheduled/archived statuses.
- Public visibility rules для published страниц.
- Page revisions при сохранении.
- SEO fields на страницу.

### Frontend Rendering

- Frontend page route по URI.
- Renderer для JSON-контента.
- Поддержка базовых блоков: `heading`, `text`, `button`, `divider`, `faq`, `html`.
- Sitemap по опубликованным страницам.
- `robots.txt` endpoint.

### Builder

- Simple builder UI.
- Advanced builder UI.
- 60+ block definitions через registry.
- Responsive preview.
- Revisions restore.
- Export/import sections.
- Template apply.

### Custom Fields

- `custom_fields_json` на странице.
- Field groups.
- Reusable presets.
- Apply/save/update/delete preset workflow в форме страницы.
- Field group template/scope rules: `all_pages`, `template`, `except_template`.
- Filtering presets by current page template in page edit/create UI.

### Media

- Upload.
- Metadata edit.
- Delete.
- SVG sanitization.

## P0 Check

P0 из `docs/unimplemented-functions-plan.md` сейчас оценивается так:

- `Pages CRUD`: реализовано.
- `Slug и URI generation`: реализовано.
- `Page revisions`: реализовано.
- `Frontend renderer`: реализовано.
- `SEO fields`: реализовано.
- `Sitemap`: реализовано на реальных данных.
- `robots.txt`: endpoint есть, но пока статический.
- `Media upload`: реализовано.
- `Cache clear`: реализовано.
- `Published page public access`: реализовано в коде.
- `Draft hidden from public`: реализовано в коде.

Вывод:

- P0 можно считать `implemented in code`.
- P0 нельзя честно считать `verified complete`, пока не прогнан реальный сценарий через runtime.

## Ограничения проверки

- `php` отсутствует в PATH текущего окружения.
- Из-за этого не выполнены `php artisan serve`, `php artisan migrate`, `php -l`.
- Полный end-to-end smoke test не прогнан.

## Next Recommendation

Следующий логичный слой: `visual block templating library`.

Почему это лучше сейчас:

- `custom_fields_json` уже есть;
- field groups и presets уже есть;
- template/scope rules для field groups уже добавлены;
- custom-fields subsystem теперь выглядит цельным и достаточно завершённым для следующего слоя builder-функциональности.

Следующий шаг:

- перейти к visual block templating library с хранением пользовательских block templates в БД.
