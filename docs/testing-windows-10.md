# Развёртывание тестовой среды VertexCMS на Windows 10

Инструкция рассчитана на локальную проверку backend, интерфейса администратора, конструктора страниц и медиатеки. Для production-развёртывания используйте отдельную серверную конфигурацию.

## 1. Рекомендуемый вариант

Для Windows 10 проще всего использовать нативный набор инструментов:

- Git for Windows;
- PHP 8.2 или новее;
- Composer 2;
- Node.js 20 LTS или новее с npm;
- SQLite для автоматических тестов;
- MySQL или SQLite для ручной browser-проверки;
- Chrome, Edge или Firefox с DevTools.

Удобнее всего получить PHP и MySQL через Laragon. Допустима отдельная установка PHP, но тогда расширения необходимо включать вручную. Docker Desktop или WSL2 также подходят, однако для первой проверки они не обязательны.

> Все команды ниже выполняются в **PowerShell** из корня проекта. Не запускайте PowerShell от администратора без необходимости.

## 2. Проверка системных требований

Откройте новое окно PowerShell и выполните:

```powershell
git --version
php --version
composer --version
node --version
npm --version
```

Проект требует PHP 8.2+. Проверьте активные расширения:

```powershell
php --ini
php -m
```

В активном `php.ini` должны быть включены как минимум:

```ini
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=pdo_sqlite
extension=sqlite3
```

Также необходимы стандартные расширения `ctype`, `dom`, `filter`, `json`, `session`, `tokenizer` и `xml`. После изменения `php.ini` закройте и заново откройте PowerShell, затем повторите `php -m`.

Если команда `php` не найдена, добавьте каталог PHP в пользовательскую переменную `Path`. Для Laragon это обычно каталог вида `C:\laragon\bin\php\php-8.2.x`.

## 3. Получение проекта

```powershell
cd C:\projects
git clone <URL-РЕПОЗИТОРИЯ> Vertex-CMS
cd Vertex-CMS
```

Если проект получен архивом, распакуйте его в каталог без кириллицы и по возможности без пробелов, например `C:\projects\Vertex-CMS`.

## 4. Установка зависимостей

```powershell
composer install
npm ci
```

Если `npm ci` сообщает, что lock-файл не соответствует `package.json`, не удаляйте `package-lock.json`: сначала обновите ветку и повторите установку. Команда `npm install` изменяет lock-файл и не должна использоваться только ради запуска тестов.

## 5. Подготовка локального окружения

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### Вариант A: SQLite — быстрый локальный запуск

Создайте файл базы данных:

```powershell
New-Item -ItemType File -Force database\database.sqlite
```

Измените в `.env` параметры базы:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=C:/projects/Vertex-CMS/database/database.sqlite
```

Для абсолютного пути используйте прямые слеши `/`. Удалите или закомментируйте `DB_HOST`, `DB_PORT`, `DB_USERNAME` и `DB_PASSWORD`.

### Вариант B: MySQL — проверка окружения, близкого к production

Создайте пустую базу `vertexcms_test` в Laragon, Adminer или консоли MySQL и укажите:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vertexcms_test
DB_USERNAME=root
DB_PASSWORD=
```

Не используйте production-базу: команды миграции и тестовые сценарии могут удалять данные.

## 6. Инициализация CMS для ручной проверки

```powershell
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan storage:link
```

Для локальной среды установите в `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
VERTEX_INSTALLED=true
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

Если приложение продолжает перенаправлять на `/install`, создайте lock-файл:

```powershell
'{"installed_at":"local-testing"}' | Set-Content -Encoding UTF8 storage\installed.lock
php artisan optimize:clear
```

Текущий `UserSeeder` создаёт пользователя `admin@example.com` с паролем `password`, но не назначает ему роль. Для локальной проверки назначьте роль `super-admin`:

```powershell
php artisan tinker --execute='$user = App\Models\User::where("email", "admin@example.com")->firstOrFail(); $role = App\Models\Role::where("slug", "super-admin")->firstOrFail(); $user->roles()->sync([$role->id]);'
```

Эти данные предназначены только для локальной машины. Не используйте пароль `password` в доступном извне окружении.

## 7. Автоматические тесты

`phpunit.xml` уже переключает тесты на SQLite `:memory:`, синхронную очередь, array cache и array session. Основной `.env` и локальная MySQL-база для PHPUnit не используются.

Запуск всего набора:

```powershell
php artisan test
```

Запуск отдельных областей:

```powershell
php artisan test --filter=Builder
php artisan test tests\Feature\MediaLibraryApiTest.php
php artisan test tests\Feature\BuilderLibraryManagerTest.php
```

Остановка после первой ошибки и подробный вывод:

```powershell
php artisan test --stop-on-failure
vendor\bin\phpunit --testdox
```

Проверка PHP-стиля:

```powershell
vendor\bin\pint --test
```

Проверка production-сборки frontend:

```powershell
npm run build
```

После тестов рабочее дерево не должно содержать случайно изменённых зависимостей или кэша:

```powershell
git status --short
```

## 8. Запуск приложения в браузере

Откройте два окна PowerShell.

В первом:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Во втором для разработки интерфейса:

```powershell
npm run dev
```

Откройте:

- сайт: `http://127.0.0.1:8000`;
- админ-панель: `http://127.0.0.1:8000/admin`;
- медиатеку: `http://127.0.0.1:8000/admin/media`.

Для проверки production-ассетов остановите `npm run dev`, выполните `npm run build`, затем обновите страницу с очисткой кэша (`Ctrl+F5`).

## 9. Проверка конструктора страниц

1. Войдите пользователем с ролью `editor` или `super-admin`.
2. Создайте страницу и откройте advanced builder.
3. Примените шаблоны Hero, Landing, Feature, CTA и FAQ.
4. Измените текст, фон, цвета и отступы в inspector.
5. Перетащите секции и блоки на canvas и в дереве элементов.
6. Проверьте desktop, tablet и mobile preview.
7. Проверьте undo/redo, autosave, ручное сохранение и preview.
8. Опубликуйте страницу и сравните публичный результат с preview.

В DevTools не должно быть красных ошибок Vue, ответов `404/419/422/500` на сохранение или запросов к отсутствующим ассетам.

## 10. Проверка медиатеки

1. Откройте `/admin/media` пользователем со всеми разрешениями `media.*`.
2. Загрузите JPG, PNG, WebP, SVG и PDF через кнопку и drag-and-drop.
3. Проверьте поиск по имени, title, alt и подписи.
4. Проверьте фильтры файлов и сортировку по дате, имени и размеру.
5. Создайте папку и подпапку, измените их цвет.
6. Выделите несколько файлов, переместите их в папку и удалите тестовые файлы массовой операцией.
7. Откройте вложение и сохраните title, alt, подпись и папку.
8. Откройте media picker из Hero или Image блока конструктора и выберите файл.

Во вкладке Network проверьте запросы к `/admin/api/media`, `/admin/api/media/folders`, `/admin/media/bulk-move` и `/admin/media/bulk-delete`.

## 11. Типичные ошибки Windows 10

### `could not find driver`

В CLI используется другой `php.ini` или выключены `pdo_sqlite`/`sqlite3` либо `pdo_mysql`. Узнайте активный файл через `php --ini`, включите расширение и перезапустите терминал.

### `Class ... not found` после переключения ветки

```powershell
composer dump-autoload
php artisan optimize:clear
```

### Ошибка Vite manifest или отсутствующий asset

```powershell
Remove-Item -Recurse -Force public\build -ErrorAction SilentlyContinue
npm ci
npm run build
```

### HTTP 419 при загрузке или сохранении

Проверьте `APP_URL`, очистите cookies для `127.0.0.1`, выполните `php artisan optimize:clear` и войдите заново. Не смешивайте в одной сессии адреса `localhost` и `127.0.0.1`.

### Ошибка записи в `storage`

Убедитесь, что каталог не находится в защищённой папке Windows и не имеет атрибута read-only:

```powershell
attrib -R storage\* /S /D
attrib -R bootstrap\cache\* /S /D
```

### Длинные пути Git

Откройте PowerShell от администратора один раз и выполните:

```powershell
git config --system core.longpaths true
```

Также храните проект ближе к корню диска, например `C:\projects\Vertex-CMS`.

## 12. Что приложить к отчёту об ошибке

- commit hash: `git rev-parse --short HEAD`;
- версии: `php --version`, `composer --version`, `node --version`, `npm --version`;
- команда, которая завершилась ошибкой;
- полный текст ошибки и соответствующий фрагмент `storage\logs\laravel.log`;
- URL и HTTP status неуспешного запроса из Network;
- скриншот интерфейса и Console;
- минимальные шаги воспроизведения;
- используемые роль, браузер и тип БД без паролей и секретов.

Перед передачей логов удалите cookies, токены, пароли, API-ключи и другие персональные данные.
