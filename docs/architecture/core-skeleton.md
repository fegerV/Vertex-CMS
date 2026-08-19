# VertexCMS Core Skeleton

## Цель

Core Skeleton задаёт технический каркас VertexCMS MVP v0.1. Его задача - дать проекту устойчивую Laravel-основу, модульное разделение и понятные точки расширения до того, как начнётся полноценная реализация installer, auth, admin, pages, builder, SEO, media, API, AI и PWA.

## Базовые принципы

- Laravel остаётся основным приложением и точкой сборки.
- Модули живут внутри монолита, но имеют собственные namespace, контроллеры, сервисы и будущие политики.
- Публичный сайт рендерится сервером через Blade и renderer страниц.
- Админ-панель может использовать Inertia + Vue 3.
- API проектируется заранее, чтобы его можно было использовать для мобильных приложений и внешних интеграций.
- Все системные настройки хранятся в базе и доступны через сервис настроек.
- Installer должен быть доступен только до установки системы.

## Модульная структура

- `app/Core` - ядро, настройки, базовые сервисы, middleware, регистрация маршрутов.
- `app/Admin` - dashboard, layout, меню админки, виджеты, уведомления.
- `app/Auth` - вход, выход, сессии, восстановление доступа.
- `app/Content` - страницы, статусы, slug, URI, ревизии.
- `app/Builder` - JSON-структура страниц, блоки, canvas, renderer.
- `app/Seo` - meta, sitemap, robots.txt, redirects, SEO-проверки.
- `app/Media` - загрузка файлов, библиотека, папки, thumbnails.
- `app/Performance` - page cache, settings cache, menu cache, очистка кеша.
- `app/System` - installer, системная информация, activity logs.

## Точки входа

- `routes/web.php` - публичный сайт, sitemap, robots.txt.
- `routes/admin.php` - админ-панель.
- `routes/api.php` - API для админки, мобильных приложений и интеграций.
- `routes/install.php` - web installer.
- `config/vertex.php` - системная конфигурация VertexCMS.

## Middleware

- `EnsureInstalled` перенаправляет неустановленную систему на installer.
- `EnsureNotInstalled` запрещает повторный запуск installer после установки.
- `RequirePermission` проверяет permission пользователя перед опасными действиями.

## Сервисы ядра

- `InstallationService` проверяет статус установки и требования окружения.
- `SettingsService` читает настройки из базы и кеширует их.
- `SlugService` создаёт безопасные slug для страниц, папок и сущностей.

## Минимальный запуск после установки PHP

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan route:list
php artisan test
```

## Ближайшие технические задачи

1. Довести Laravel bootstrap до полностью исполняемого состояния после `composer install`.
2. Реализовать web installer: проверка окружения, БД, запись `.env`, миграции, первый администратор, `installed.lock`.
3. Реализовать auth flow для `/admin/login` и `/admin/logout`.
4. Добавить admin layout на Inertia + Vue 3.
5. Подключить RBAC к route groups, UI и API.
6. Реализовать CRUD страниц и генерацию `uri`.
7. Добавить renderer JSON-страниц и первые Blade partials блоков.
