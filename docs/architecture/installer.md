# Installer Architecture

## Цель

Web installer должен позволять установить VertexCMS без SSH. Пользователь проходит шаги в браузере, вводит настройки базы, сайта и первого администратора, после чего система создаёт `.env`, запускает миграции, seeders и блокирует повторную установку.

## Routes

```txt
GET /install
POST /install/check-requirements
POST /install/check-database
POST /install/save-config
POST /install/run
```

Все installer routes доступны только до установки системы. После установки `EnsureNotInstalled` возвращает `403`.

## Installation lock

Система считается установленной, если выполнено хотя бы одно условие:

```txt
VERTEX_INSTALLED=true
storage/app/installed.lock существует
```

`installed.lock` хранит:

```json
{
  "installed_at": "2026-05-07T00:00:00.000000Z",
  "installed_by": 1,
  "install_id": "uuid",
  "version": "0.1.0"
}
```

## Backend services

- `InstallationService` проверяет требования окружения и installed status.
- `DatabaseConnectionService` проверяет подключение к MySQL через PDO.
- `EnvironmentFileService` создаёт или обновляет `.env`.
- `InstallerRunner` запускает финальную установку.

## Installer flow

1. `GET /install` показывает первый экран и текущие требования.
2. `POST /install/check-requirements` возвращает список requirements.
3. `POST /install/check-database` проверяет подключение к БД.
4. `POST /install/save-config` сохраняет базовые настройки в `.env`.
5. `POST /install/run` выполняет установку.

## Payload для `/install/run`

```json
{
  "DB_HOST": "127.0.0.1",
  "DB_PORT": 3306,
  "DB_DATABASE": "vertexcms",
  "DB_USERNAME": "root",
  "DB_PASSWORD": "",
  "site_name": "VertexCMS",
  "site_url": "https://example.com",
  "site_locale": "ru",
  "site_timezone": "Europe/Moscow",
  "site_admin_email": "admin@example.com",
  "admin_name": "Admin",
  "admin_email": "admin@example.com",
  "admin_password": "password",
  "admin_password_confirmation": "password"
}
```

## Что делает `/install/run`

- проверяет требования окружения;
- проверяет подключение к БД;
- записывает `.env`;
- генерирует `APP_KEY`, если ключ пустой;
- применяет миграции;
- запускает seeders;
- создаёт первого Super Admin;
- создаёт базовые настройки сайта;
- создаёт стартовую опубликованную страницу `/`;
- создаёт `storage/app/installed.lock`.

## Error handling

- Если requirements не пройдены, возвращается `422`.
- Если БД недоступна, возвращается `422`.
- Если установка падает во время миграций или seeders, возвращается `500`, ошибка логируется.
- Повторный запуск после установки запрещён.

## Acceptance criteria

- Пользователь может пройти установку через браузер.
- Без write permissions установка не продолжается.
- Без подключения к БД установка не продолжается.
- После установки можно войти в админку созданным пользователем.
- После установки `/install` больше недоступен.
