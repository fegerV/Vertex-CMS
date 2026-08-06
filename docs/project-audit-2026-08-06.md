# Аудит VertexCMS — 2026-08-06

## Резюме

Проект уже содержит значительный объём функциональности, но текущую ветку нельзя
считать готовой к production или даже к надёжному развёртыванию без исправления
нескольких блокирующих дефектов. Главные риски находятся не в количестве
реализованных функций, а в загрузке приложения, контроле доступа, воспроизводимости
сборки и согласованности документации с репозиторием.

Аудит выполнен статически по коду и конфигурации, с запуском доступных проверок.
Полный Laravel test suite запустить не удалось: каталог `vendor/` отсутствует, а
`composer.lock` не соответствует `composer.json`.

## P0 — блокирующие ошибки

### 1. `routes/api.php` не проходит синтаксическую проверку

В одном файле импортированы два разных класса с одинаковым коротким именем
`AIController`:

- `App\AI\Http\Controllers\AiController`;
- `App\Http\Controllers\Api\AIController`.

PHP завершает загрузку fatal error `name is already in use`. Поскольку файл
подключается при bootstrap маршрутов, это блокирует запуск приложения, Artisan и
тестов после установки зависимостей.

**Исправление:** назначить явные aliases (например, `DraftAiController` и
`ApiAiController`), обновить ссылки в маршрутах и добавить smoke-test загрузки
route collection (`php artisan route:list`).

### 2. Административный Pages API доступен без аутентификации и permissions

CRUD-маршруты `/api/pages` объявлены вне middleware-группы. Они позволяют читать
черновики и удалённые страницы, создавать, изменять и удалять контент. Аналогично
без route middleware объявлены builder preview, AI draft endpoints, system info и
очистка кеша. В AI-контроллере есть локальная проверка прав, но для остальных
маршрутов единый защитный слой отсутствует.

**Исправление:** поместить административные endpoints под `auth:sanctum` и
granular permission middleware; публичное чтение оставить только в
версионированном `/api/v1/public` contract. Добавить feature-tests с матрицей
anonymous / authenticated without permission / permitted user.

### 3. `PageApiController::destroy()` использует необъявленную переменную

Метод принимает только `Page $page`, но обращается к `$request->user()`. После
достижения endpoint операция завершится `Undefined variable $request` вместо
контролируемого ответа.

**Исправление:** добавить `Request $request` в сигнатуру и тест успешного удаления;
одновременно закрыть endpoint middleware и policy/permission проверкой.

## P1 — высокие риски

### 4. Dependency lock не воспроизводит заявленный состав зависимостей

`composer validate --strict` сообщает, что `composer.lock` устарел, а обязательный
dev-пакет `darkaonline/l5-swagger` в lock-файле отсутствует. Чистая установка через
`composer install` поэтому не гарантирует окружение, описанное в `composer.json`.

**Исправление:** выполнить осознанный `composer update darkaonline/l5-swagger
--with-all-dependencies`, проверить diff lock-файла и затем прогнать полный suite
на PHP 8.2 и поддерживаемой СУБД.

### 5. `.gitignore` повреждён, зависимости и build artifacts хранятся в Git

Файл `.gitignore` содержит текстовое пояснение и пустой Markdown code fence вместо
паттернов. В результате Git отслеживает около 6250 файлов из `node_modules`, а
также `public/build` и архив `vertex-cms.zip`. Это раздувает историю, создаёт шум
после `npm install/build`, повышает риск merge-конфликтов и затрудняет supply-chain
аудит.

**Исправление:** восстановить Laravel/Vite `.gitignore`; удалить из индекса
`node_modules`, локальные env/runtime файлы и определить единую политику для
`public/build` и release-архивов. Выполнить это отдельным housekeeping PR, чтобы
не смешивать тысячи удалений с функциональными изменениями.

### 6. Небезопасные границы вывода HTML требуют формальной политики

Публичные Blade views выводят готовый `$html`, HTML form fields, consent text,
custom CSS и email content через `{!! !!}`. Часть builder-блоков корректно сочетает
raw output с `e()`, но для пользовательского HTML и импортируемых form definitions
доверительная граница не документирована и sanitizer не очевиден.

**Исправление:** ввести единый `HtmlSanitizer` с allowlist-профилями для редактора,
форм и email; санитизировать при записи и, для legacy data, при выводе; запретить
опасные URL schemes, inline event handlers и закрывающие `style`/`script` payloads.
Добавить XSS regression tests для builder, forms и schema JSON.

### 7. System endpoints раскрывают сведения и изменяют состояние без защиты

`GET /api/system/info` возвращает результат `SystemInfoService`, а
`POST /api/cache/clear` очищает application/page cache. Оба маршрута публичны.
Даже если часть глобальных middleware ограничит доступ после установки, это не
заменяет явную authentication/authorization boundary.

**Исправление:** требовать `auth:sanctum` и permissions `system.view` /
`system.cache.clear`, журналировать очистку и rate-limit mutation endpoint.

## P2 — качество и архитектура

### 8. API смешивает несколько контрактов в одном неструктурированном файле

В `routes/api.php` одновременно находятся legacy page CRUD, session-based media,
builder, два AI API, analytics и module routes. Часть использует session `web`,
часть Sanctum, часть controller-level permission checks, часть не защищена.

**Улучшение:** разделить маршруты по bounded contexts и версии; задать общие
middleware stacks и name prefixes; оставить `routes/api.php` композитором файлов,
а не местом определения всего API.

### 9. Документация о тестах устарела и создаёт ложную уверенность

`docs/status/current-status.md` фиксирует несколько разных исторических результатов
вплоть до `78 tests / 1239 assertions`, но текущая checkout не может загрузить
Artisan без `vendor`, route-файл содержит fatal error, а lock-файл невалиден.

**Улучшение:** отделить historical verification от current CI status, добавить
badge/ссылку на workflow и обновлять число тестов автоматически. Любой release
status должен ссылаться на commit SHA и окружение.

### 10. Нет единого обязательного quality gate

В `package.json` есть только `dev` и `build`; отсутствуют lint/typecheck/unit scripts
для frontend. В Composer scripts есть Laravel tests, но нет Pint, статического
анализа, проверки Blade/routes и security audit.

**Улучшение:** создать CI pipeline со следующими независимыми jobs:

1. `composer validate --strict` и `composer audit`;
2. PHP syntax + Pint check + PHPStan/Larastan;
3. migrations на SQLite и MySQL, затем `php artisan test`;
4. `npm ci`, ESLint, frontend unit tests и `npm run build`;
5. route/bootstrap smoke test и проверка отсутствия secrets/artifacts.

## Предлагаемый план работ

### Этап 1 — сделать main branch запускаемой (1 PR)

1. Устранить конфликт импортов AI controllers.
2. Исправить сигнатуру `PageApiController::destroy()`.
3. Закрыть legacy admin API аутентификацией и permissions.
4. Обновить lock-файл.
5. Добавить route smoke test и security tests для критичных endpoints.

### Этап 2 — восстановить воспроизводимость (отдельный PR)

1. Исправить `.gitignore`.
2. Удалить `node_modules` и release archive из Git index.
3. Принять решение: build assets либо собираются CI/release job, либо коммитятся
   только детерминированным release-процессом.
4. Добавить CI и dependency caches на основе lock-файлов.

### Этап 3 — усилить безопасность (2–3 PR)

1. Провести инвентаризацию всех routes и составить permission matrix.
2. Ввести policies для моделей вместо разрозненных controller checks.
3. Реализовать и протестировать HTML sanitization boundary.
4. Добавить rate limits, audit logs и negative authorization tests.

### Этап 4 — снизить стоимость развития

1. Разделить API routes и controllers по версиям/контекстам.
2. Добавить Larastan, ESLint и frontend tests.
3. Синхронизировать roadmap/status с проверяемыми CI artifacts.
4. После стабилизации вернуться к runtime QA builder/forms/PWA, уже обозначенному
   в roadmap.

## Выполненные проверки

| Проверка | Результат |
| --- | --- |
| `php -l routes/api.php` | Ошибка: конфликт двух импортов `AIController` |
| `composer validate --strict` | Ошибка: lock устарел, отсутствует `l5-swagger` |
| `php artisan test` | Не запущен: отсутствует `vendor/autoload.php` |
| `php artisan route:list --except-vendor` | Не запущен: отсутствует `vendor/autoload.php` |
| `npm run build` | Успешно |
| поиск debug-вызовов `dd/dump/var_dump/print_r` | В application code не найдено |
| инвентаризация tracked `node_modules` | Около 6250 файлов |

## Критерии готовности ближайшего стабилизационного релиза

- чистый clone успешно проходит `composer install` и `npm ci`;
- `php artisan route:list` и все migrations завершаются без ошибок;
- anonymous client не может читать drafts/system info или изменять данные;
- полный backend/frontend suite зелёный в CI на зафиксированном commit SHA;
- в Git нет зависимостей, secrets, runtime data и случайных release artifacts;
- все raw HTML entry points имеют явный sanitizer contract и XSS tests.
