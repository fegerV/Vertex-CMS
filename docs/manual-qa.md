# Manual QA Checklist

Актуально на `2026-05-12`.

Automated suite уже зелёный:

- `35 tests`
- `236 assertions`
- покрытие: `P0`, `P2`, `P3`, `P4`, `P5`

Этот чек-лист закрывает то, что ещё не подтверждается unit/feature тестами.

## P0 - Core CMS Flow

- Открыть `/install` на чистой среде и пройти установку до создания первого `Super Admin`.
- Войти в `/admin` и убедиться, что dashboard открывается без runtime-ошибок.
- Создать страницу, сохранить как `draft`, убедиться, что публичный URL не открывается.
- Опубликовать страницу и убедиться, что публичный URL рендерит `content_json`.
- Проверить, что после повторного сохранения появляется новая revision.
- Открыть `/sitemap.xml` и убедиться, что опубликованная индексируемая страница попала в sitemap.
- Открыть `/robots.txt` и убедиться, что ответ отдается корректно.

## P1 - Admin and RBAC

- Проверить sidebar и доступ к route для `viewer`, `editor`, `admin`, `super-admin`.
- Убедиться, что `viewer` не может сохранить settings, users, taxonomies и pages.
- Убедиться, что `editor` не видит и не открывает users/roles/system.
- Убедиться, что `super-admin` видит `System`, `Cache`, `Logs`, `Analytics`.
- Проверить light/dark theme toggle, mobile sidebar и read-only states на settings screen.

## P2 - Page Builder

Для полноценной проверки нужен установленный проект с доступной БД, собранными Vite-ассетами, учётной записью `editor` или `super-admin` и браузером с DevTools. Проверку следует проводить на desktop, tablet и mobile режимах конструктора.

- Создать страницу через basic builder: добавить блоки, сохранить draft, открыть preview.
- Опубликовать builder-страницу и проверить публичный рендер.
- В advanced builder проверить drag-and-drop секций и блоков.
- Повторить перенос секций и блоков через дерево элементов, включая перенос блока между секциями и сброс в конец пустой секции.
- Применить встроенные заготовки `Hero`, `Лендинг`, `Преимущество`, `CTA` и `FAQ`; проверить, что у каждого добавленного блока открываются настройки.
- Отредактировать тексты, цвета, фон hero и отступы секций, затем сравнить editor, live-preview и публичную страницу.
- Проверить inline block actions, quick add, multi-select и batch actions.
- Проверить presets/templates/shared libraries: create, edit, apply, delete.
- Проверить media picker, включая вложенные repeater-поля.
- Проверить undo/redo, keyboard shortcuts, command palette и context menu.
- Проверить revisions, autosave, export/import sections.

## P3 - API v1

- Выполнить `POST /api/v1/auth/login` и получить bearer token.
- С bearer token открыть `GET /api/v1/me`.
- Проверить `GET /api/v1/auth/tokens` и revoke токена через `DELETE /api/v1/auth/tokens/{id}`.
- Проверить `GET /api/v1/public/pages`, `/by-uri`, `/menus/{location}`, `/settings/site`.
- Убедиться, что success/error responses имеют стабильный envelope и `meta.api_version`.
- Проверить OpenAPI draft против фактических ответов API.

## Media Library

- Открыть медиатеку под пользователем с правами `media.view`, `media.upload`, `media.edit`, `media.delete` и `media.manage_folders`.
- Проверить загрузку одиночного файла и пачки файлов через системный picker и drag-and-drop.
- Проверить поиск, фильтры `Изображения`/`PDF`/`Документы` и все варианты сортировки; после смены фильтра сетка и пагинация должны обновиться.
- Выделить несколько плиток чекбоксами, переместить их в папку, затем выполнить массовое удаление тестовых файлов.
- Открыть карточку вложения, изменить title, alt, подпись и папку; убедиться, что изменения видны после обновления.
- Повторить выбор изображения из page builder: выбранный файл должен вернуться в inspector без перезагрузки страницы.

## P4 - AI Module

- Под `super-admin` сохранить AI keys в settings и убедиться, что ключи маскируются в UI.
- Под `admin` без `ai.manage_keys` убедиться, что AI secret fields недоступны для редактирования.
- Под `editor` с `ai.use` открыть AI panel на create/edit page.
- Проверить actions `text`, `faq`, `cta`, `seo`, `builder` и убедиться, что изменения применяются только как draft.
- Убедиться, что AI не сохраняет страницу без явного подтверждения пользователя.
- Проверить activity logs и убедиться, что secret keys, raw prompts и raw responses не утекли при отключенном storage.
- Повторить ключевые сценарии с реальным внешним provider SDK и живым API key.

## P5 - PWA, Theme, Taxonomy

- Открыть `manifest.webmanifest` и проверить, что значения соответствуют settings.
- Проверить регистрацию `service-worker.js` и offline fallback в браузере после отключения сети.
- Убедиться, что theme fallback корректно рендерит page, offline и term archive views.
- Создать taxonomy и term из админки, привязать term к странице, открыть public term archive.
- Проверить canonical/meta/robots и sitemap inclusion для term archive.
- Проверить `GET /api/v1/public/taxonomies` и `GET /api/v1/public/taxonomies/{taxonomy}/terms/{term}/pages`.

## Analytics

- Открыть несколько public pages и term archives.
- Проверить, что визиты появились в admin analytics dashboard.
- Сравнить top pages/top term archives с фактически посещёнными URL.
