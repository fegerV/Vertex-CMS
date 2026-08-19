# Документация Администратора CMS

## Оглавление

1. [Введение](#введение)
2. [Установка CMS](#установка-cms)
3. [Первичная настройка](#первичная-настройка)
4. [Безопасность и Compliance](#безопасность-и-compliance)
5. [Управление производительностью](#управление-производительностью)
6. [Маркетинг и SEO инструменты](#маркетинг-и-seo-инструменты)
7. [Мультиязычность и GraphQL API](#мультиязычность-и-graphql-api)
8. [Управление медиа (DAM)](#управление-медиа-dam)
9. [E-commerce функциональность](#e-commerce-функциональность)
10. [Аналитика и BI](#аналитика-и-bi)
11. [Логирование и мониторинг](#логирование-и-мониторинг)
12. [Социальные интеграции и уведомления](#социальные-интеграции-и-уведомления)
13. [AI и автоматизация](#ai-и-автоматизация)
    - [RAG Консультант (AI Chat Bot)](#rag-консультант-ai-chat-bot)
    - [AI Site Wizard - Мастер создания сайта](#ai-site-wizard-мастер-создания-сайта)
    - [Авто-заполнение контента](#авто-заполнение-контента)
    - [AI чат-бот](#ai-чат-бот)
    - [Умный поиск](#умный-поиск)
    - [Генерация изображений](#генерация-изображений)
14. [Архитектурные улучшения](#архитектурные-улучшения)
15. [Надежность и Disaster Recovery](#надежность-и-disaster-recovery)
16. [Экосистема и Marketplace](#экосистема-и-marketplace)

---

## Введение

Данная документация предназначена для администраторов сайта и описывает полный цикл работы с CMS: от установки до продвинутой настройки всех модулей системы.

### Требования к серверу

**Минимальные требования:**
- PHP 8.1+
- MySQL 8.0+ / PostgreSQL 14+
- Redis 6.0+
- Node.js 18+ (для сборки фронтенда)
- 2 GB RAM
- 10 GB дискового пространства

**Рекомендуемые требования:**
- PHP 8.2+
- MySQL 8.0+ / PostgreSQL 15+
- Redis 7.0+
- Node.js 20+
- 4 GB RAM
- 50 GB дискового пространства
- SSL сертификат
- CDN (Cloudflare, CloudFront)

---

## Установка CMS

### Шаг 1: Подготовка окружения

```bash
# Клонируйте репозиторий
git clone https://github.com/your-org/cms.git
cd cms

# Установите зависимости PHP
composer install --no-dev --optimize-autoloader

# Установите зависимости Node.js
npm install
```

### Шаг 2: Настройка базы данных

```bash
# Создайте базу данных
mysql -u root -p
CREATE DATABASE cms_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cms_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON cms_production.* TO 'cms_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Шаг 3: Конфигурация окружения

Скопируйте файл `.env.example` в `.env` и настройте параметры:

```ini
APP_NAME="CMS"
APP_ENV=production
APP_KEY=base64:your_app_key_here
APP_URL=https://yourdomain.com
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms_production
DB_USERNAME=cms_user
DB_PASSWORD=strong_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls

# Security
SESSION_SECURE_COOKIE=true
CSRF_PROTECTION=true
XSS_PROTECTION=true

# CDN
CDN_URL=https://cdn.yourdomain.com

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Шаг 4: Инициализация приложения

```bash
# Генерация ключа приложения
php artisan key:generate

# Миграция базы данных
php artisan migrate --force

# Создание символьных ссылок
php artisan storage:link

# Оптимизация для production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Сборка фронтенда
npm run build

# Создание супер-администратора
php artisan make:superadmin
```

### Шаг 5: Настройка веб-сервера (Nginx)

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    
    root /var/www/cms/public;
    index index.php;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Шаг 6: Настройка очередей (Supervisor)

Создайте файл `/etc/supervisor/conf.d/cms-worker.conf`:

```ini
[program:cms-worker]
command=php /var/www/cms/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/cms/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cms-worker:*
```

### Шаг 7: Настройка Cron

```bash
crontab -e
# Добавьте задачу:
* * * * * cd /var/www/cms && php artisan schedule:run >> /dev/null 2>&1
```

### Шаг 8: Проверка установки

Посетите `https://yourdomain.com/admin/install-check` для проверки всех требований.

---

## Первичная настройка

### Вход в панель администратора

1. Перейдите по адресу `https://yourdomain.com/admin`
2. Введите учетные данные супер-администратора
3. При первом входе система потребует сменить пароль и настроить 2FA

### Мастер первоначальной настройки

После первого входа запустится мастер настройки:

1. **Базовая информация о сайте**
   - Название сайта
   - Логотип и фавикон
   - Описание и мета-теги
   - Контакты

2. **Настройки безопасности**
   - Принудительное использование HTTPS
   - Настройка 2FA для администраторов
   - IP whitelist для админ-панели

3. **Производительность**
   - Выбор драйвера кэша (Redis рекомендуется)
   - Настройка CDN
   - Включение сжатия

4. **Локализация**
   - Язык по умолчанию
   - Дополнительные языки

5. **Медиа настройки**
   - Максимальный размер загружаемых файлов
   - Разрешенные типы файлов
   - Настройки оптимизации изображений

### Настройка профиля администратора

Перейдите в `Администрирование → Пользователи → Мой профиль`:

- Загрузите фото профиля
- Настройте уведомления
- Включите двухфакторную аутентификацию (2FA)
- Сохраните резервные коды восстановления

**Включение 2FA:**
1. Отсканируйте QR-код в приложении Google Authenticator или Authy
2. Введите 6-значный код из приложения
3. Сохраните резервные коды в безопасном месте

---

## Безопасность и Compliance

### GDPR/Cookie Consent баннер

**Настройка:**

1. Перейдите в `Настройки → Privacy → Cookie Consent`
2. Включите баннер cookie
3. Настройте текст сообщения
4. Выберите стиль отображения (banner, modal, floating)
5. Настройте категории cookies:
   - Необходимые (always active)
   - Аналитические
   - Маркетинговые
   - Функциональные

**Пример конфигурации:**

```json
{
  "enabled": true,
  "style": "floating-bottom",
  "message": "Мы используем файлы cookie для улучшения работы сайта.",
  "acceptButtonText": "Принять",
  "rejectButtonText": "Отклонить",
  "settingsButtonText": "Настройки",
  "categories": {
    "necessary": {
      "enabled": true,
      "required": true,
      "description": "Необходимы для базовой функциональности"
    },
    "analytics": {
      "enabled": true,
      "required": false,
      "description": "Помогают понять, как используется сайт"
    },
    "marketing": {
      "enabled": true,
      "required": false,
      "description": "Используются для показа рекламы"
    }
  }
}
```

### Визуальный редактор ролей и прав доступа (RBAC)

**Создание роли:**

1. Перейдите в `Администрирование → Роли и разрешения`
2. Нажмите "Создать роль"
3. Введите название и описание
4. Используйте визуальный интерфейс для назначения прав

**Группы прав:**
- Контент (создание, редактирование, удаление, публикация)
- Медиа (загрузка, редактирование, удаление)
- Пользователи (просмотр, создание, редактирование, удаление)
- Настройки (чтение, запись)
- E-commerce (товары, заказы, платежи)
- Отчеты (просмотр, экспорт)

**Пример роли "Контент-менеджер":**
- ✓ Контент: создание, редактирование
- ✓ Контент: публикация (только свои материалы)
- ✓ Медиа: загрузка, редактирование
- ✗ Контент: удаление
- ✗ Пользователи: управление
- ✗ Настройки: изменение

**Назначение роли пользователю:**
1. Перейдите в `Администрирование → Пользователи`
2. Выберите пользователя
3. Во вкладке "Роли" назначьте нужные роли
4. Можно назначить несколько ролей (права суммируются)

### Аудит логирование действий администраторов

**Включение аудита:**

1. Перейдите в `Настройки → Безопасность → Audit Log`
2. Включите логирование
3. Выберите события для отслеживания

**Отслеживаемые события:**
- Вход/выход из системы
- Изменение настроек сайта
- Создание/редактирование/удаление контента
- Изменение прав пользователей
- Экспорт данных
- Изменение финансовых настроек

**Просмотр логов:**

Перейдите в `Администрирование → Audit Log` для просмотра:
- Кто выполнил действие
- Какое действие выполнено
- Когда произошло
- IP адрес и User Agent
- Старые и новые значения (для изменений)

**Фильтрация и экспорт:**
- Фильтр по пользователю, дате, типу действия
- Экспорт в CSV/JSON
- Настройка уведомлений о подозрительных действиях

### IP Blacklist/Whitelist для API и админки

**Настройка Whitelist:**

1. Перейдите в `Настройки → Безопасность → IP Access Control`
2. Вкладка "Whitelist"
3. Добавьте разрешенные IP адреса или диапазоны

**Пример:**
```
# Отдельные IP
192.168.1.100
203.0.113.50

# Диапазоны CIDR
10.0.0.0/8
172.16.0.0/12
```

**Настройка Blacklist:**

1. Вкладка "Blacklist"
2. Добавьте заблокированные IP

**Автоматическое добавление при:**
- 10+ неудачных попыток входа за 5 минут
- Обнаружении SQL injection атак
- DDoS активности

**Геоблокировка:**

1. Перейдите в `Настройки → Безопасность → Geo Blocking`
2. Выберите страны для блокировки или разрешения
3. Примените к админ-панели, API или всему сайту

### CSRF Protection

CSRF защита включена по умолчанию:
- Все POST, PUT, DELETE запросы требуют CSRF токен
- Для SPA используйте sanctum/csrf-cookie endpoint
- Для мобильных приложений используйте API tokens

### XSS Protection

**Автоматическая защита:**
- Все пользовательские данные экранируются при выводе
- HTML фильтруется через HTMLPurifier
- Content-Type headers устанавливаются корректно

**Настройка уровня защиты:**

1. Перейдите в `Настройки → Безопасность → XSS Protection`
2. Выберите уровень:
   - Strict (рекомендуется)
   - Balanced
   - Permissive (только для доверенного контента)

### Content Security Policy (CSP)

**Настройка CSP:**

1. Перейдите в `Настройки → Безопасность → CSP`
2. Включите Report Only режим для тестирования
3. Настройте директивы

**Пример конфигурации:**
```
default-src 'self'
script-src 'self' 'unsafe-inline' https://www.google-analytics.com
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com
font-src 'self' https://fonts.gstatic.com
img-src 'self' data: https: blob:
connect-src 'self' https://api.yourdomain.com
object-src 'none'
frame-ancestors 'self'
```

**Отчетность:**
- URL для сбора отчетов о нарушениях
- Просмотр отчетов в `Администрирование → Безопасность → CSP Reports`

---

## Управление производительностью

### CDN Интеграция

**Настройка Cloudflare:**

1. Перейдите в `Настройки → Производительность → CDN`
2. Выберите провайдера (Cloudflare, CloudFront, KeyCDN)
3. Введите credentials

**Cloudflare настройка:**
```
Zone ID: abc123xyz
API Token: your_api_token
Email: your@email.com
```

**Настройка правил кэширования:**
- Статические assets: Cache Everything, TTL 1 месяц
- Изображения: Cache Everything, TTL 1 год
- CSS/JS: Cache Everything, TTL 1 месяц
- HTML: Bypass cache или TTL 5 мин
- API responses: Bypass cache

**Purge cache:**
- Полный purge
- Selective purge по URL
- Автоматический purge при обновлении контента

### Lazy Load

**Включение lazy load:**

1. Перейдите в `Настройки → Производительность → Lazy Load`
2. Включите для:
   - Изображений
   - Видео
   - Iframe (YouTube, Vimeo)
   - Компонентов ниже fold

**Настройки:**
```json
{
  "images": {
    "enabled": true,
    "threshold": "0px",
    "placeholder": "blur",
    "fadeIn": true
  },
  "videos": {
    "enabled": true,
    "clickToLoad": true,
    "showThumbnail": true
  }
}
```

### Кэширование

**Управление кэшем через UI:**

Перейдите в `Настройки → Производительность → Cache Management`

**Типы кэша:**
1. **Application Cache** - конфигурация, роуты, представления
2. **Data Cache** - запросы к БД, результаты вычислений
3. **Page Cache** - полные страницы, фрагменты страниц

**Очистка кэша:**
- Очистить всё
- Очистить по типу
- Очистить по тегам
- Автоматическая очистка по расписанию

**Настройка Redis:**
```json
{
  "host": "127.0.0.1",
  "port": 6379,
  "database": 0,
  "prefix": "cms_",
  "ttl": {
    "default": 3600,
    "long_term": 86400,
    "short_term": 300
  }
}
```

### Сжатие

**Gzip/Brotli сжатие:**

1. Перейдите в `Настройки → Производительность → Compression`
2. Включите Brotli (приоритет) или Gzip
3. Настройте уровни сжатия

```json
{
  "brotli": {
    "enabled": true,
    "quality": 6,
    "types": ["text/html", "text/css", "application/javascript", "application/json"]
  },
  "gzip": {
    "enabled": true,
    "level": 6
  }
}
```

**Оптимизация изображений:**

Интеграция с сервисами:
- TinyPNG
- ShortPixel
- Imagify
- Local optimization с Sharp/libvips

**Настройки:**
```json
{
  "auto_optimize": true,
  "convert_to_webp": true,
  "keep_original": true,
  "quality": 85,
  "max_width": 2560,
  "progressive": true
}
```

### Мониторинг очередей и неудачных задач

**Дашборд очередей:**

Перейдите в `Инструменты → Queue Monitor`

**Метрики:**
- Количество задач в очереди
- Среднее время выполнения
- Количество неудачных задач
- Пропускная способность

**Управление очередями:**
- Pause/Resume очереди
- Retry неудачных задач
- Delete задач
- Просмотр деталей задачи
- Логи выполнения

**Алерты:**
Настройте уведомления при:
- Более 100 задач в очереди
- Более 10 неудачных задач за час
- Задача выполняется дольше 30 минут
- Worker упал

### Health Checks дашборд

**Перейдите в `Инструменты → Health Dashboard`**

**Проверки:**
1. **База данных** - подключение, время отклика
2. **Redis** - подключение, использование памяти
3. **Файловая система** - свободное место, права доступа
4. **Сервисы** - почтовый сервер, CDN, сторонние API
5. **Производительность** - время ответа, CPU/RAM

**Настройка проверок:**
```json
{
  "checks": {
    "database": {
      "enabled": true,
      "timeout": 5,
      "warning_threshold": 1000,
      "critical_threshold": 5000
    },
    "disk": {
      "enabled": true,
      "warning_percent": 80,
      "critical_percent": 90
    }
  },
  "notifications": {
    "slack": true,
    "email": true
  }
}
```

---

## Маркетинг и SEO инструменты

### Менеджер редиректов (301/302)

**Перейдите в `Маркетинг → Redirects`**

**Создание редиректа:**
1. Нажмите "Добавить редирект"
2. Выберите тип: 301 Permanent, 302 Temporary
3. Заполните поля:
   - Source URL (старый путь)
   - Destination URL (новый путь)
   - Тип соответствия (exact, regex, wildcard)

**Примеры:**

Exact match:
```
Source: /old-page
Destination: /new-page
Type: 301
```

Regex:
```
Source: ^/blog/([0-9]+)/(.*)$
Destination: /articles/$2
Type: 301
```

Wildcard:
```
Source: /products/*
Destination: /shop/*
Type: 301
```

**Массовый импорт:**
Загрузите CSV файл с колонками: source_url, destination_url, type

**Мониторинг:**
- Статистика использования редиректов
- 404 ошибки (предложение создать редирект)
- Цепочки редиректов (предупреждения)

### Автоматическая генерация Schema.org (JSON-LD)

**Перейдите в `Маркетинг → SEO → Schema.org`**

**Типы схем:**
1. **Organization** - название, логотип, контакты, соцсети
2. **WebSite** - название сайта, URL, SearchAction
3. **Article/BlogPosting** - заголовок, автор, дата, изображение
4. **Product** - название, цена, наличие, отзывы
5. **BreadcrumbList** - автоматически для всех страниц
6. **FAQPage** - для страниц с FAQ
7. **Event** - для событий

**Конфигурация Organization:**
```json
{
  "name": "Your Company",
  "logo": "/images/logo.png",
  "sameAs": [
    "https://facebook.com/yourcompany",
    "https://twitter.com/yourcompany"
  ],
  "contactPoint": {
    "telephone": "+1-800-555-5555",
    "contactType": "customer service"
  }
}
```

**Предпросмотр:**
Для каждой страницы доступен предпросмотр JSON-LD:
- Валидация через Google Rich Results Test
- Предпросмотр в поисковой выдаче
- Рекомендации по улучшению

### A/B тестирование

**Создание теста:**

1. Перейдите в `Маркетинг → A/B Tests`
2. Нажмите "Создать тест"
3. Настройте параметры

**Параметры теста:**
```json
{
  "name": "Homepage Hero CTA",
  "type": "content",
  "url": "/",
  "variants": [
    {
      "name": "Control (A)",
      "weight": 50,
      "content": "Начать бесплатно"
    },
    {
      "name": "Variant (B)",
      "weight": 50,
      "content": "Попробовать сейчас"
    }
  ],
  "goal": {
    "type": "conversion",
    "event": "button_click"
  },
  "duration": {
    "start_date": "2024-01-15",
    "end_date": "2024-02-15",
    "min_sample_size": 1000
  }
}
```

**Типы тестов:**
1. **Content Test** - изменение текста, изображений
2. **URL Test** - разные версии страниц
3. **Multivariate Test** - комбинации изменений
4. **Redirect Test** - трафик на разные URL

**Метрики:**
- Конверсии
- CTR
- Время на странице
- Bounce rate
- Доход (для e-commerce)

### Визуальный редактор Sitemap и Robots.txt

**Sitemap редактор:**

Перейдите в `Маркетинг → SEO → Sitemap`

**Настройки:**
```json
{
  "enabled": true,
  "url": "/sitemap.xml",
  "max_urls_per_sitemap": 50000,
  "include_images": true,
  "lastmod_enabled": true,
  "changefreq_default": "weekly",
  "priority_default": 0.5
}
```

**Визуальное дерево:**
- Отображение структуры сайта
- Drag-and-drop для изменения приоритетов
- Включение/исключение разделов
- Настройка частоты обновления

**Исключения:**
```
/admin/*
/api/*
/search
/user/*
/cart
/checkout
```

**Robots.txt редактор:**

Перейдите в `Маркетинг → SEO → Robots.txt`

**Пример конфигурации:**
```
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/
Disallow: /search

Sitemap: https://yoursite.com/sitemap.xml
```

---

## Мультиязычность и GraphQL API

### Мультиязычность

**Включение мультиязычности:**

1. Перейдите в `Настройки → Локализация → Языки`
2. Включите мультиязычный режим
3. Добавьте языки

**Добавление языка:**
1. Нажмите "Добавить язык"
2. Выберите из списка или добавьте кастомный
3. Настройте параметры:

```json
{
  "code": "en",
  "name": "English",
  "locale": "en_US",
  "flag": "🇺🇸",
  "direction": "ltr",
  "enabled": true,
  "default": false,
  "fallback": "ru"
}
```

**Структура URL:**
Выберите один из вариантов:
- Поддомены: `en.yoursite.com`
- Подпапки: `yoursite.com/en/`
- Параметры: `yoursite.com?lang=en`
- TLD: разные домены для каждого языка

**Перевод контента:**

Для каждого типа контента:
- Поля для перевода
- Статус перевода (переведено/требуется перевод)
- Синхронизация с оригиналом

**Массовый перевод:**
- Экспорт контента для перевода
- Импорт переведенного контента
- Интеграция с сервисами: DeepL, Google Translate, Crowdin

**SEO для мультиязычного сайта:**
- Автоматические hreflang теги
- Локализованные meta-теги
- Перевод URL slugs
- Локализованные sitemap

### GraphQL API

**Включение GraphQL:**

1. Перейдите в `Настройки → API → GraphQL`
2. Включите GraphQL endpoint
3. Настройте доступ

**Endpoint:** `https://yourdomain.com/graphql`

**Аутентификация:**
- Public queries - без аутентификации
- Protected queries - требуется API token
- Admin queries - требуется admin token

**Примеры запросов:**

Получение локализованной страницы:
```graphql
query GetLocalizedContent($lang: String!, $slug: String!) {
  page(slug: $slug, lang: $lang) {
    id
    title
    content
    seo {
      title
      description
    }
    translations {
      locale
      url
    }
  }
}
```

Переменные:
```json
{
  "lang": "en",
  "slug": "about"
}
```

Получение товаров с фильтрами:
```graphql
query GetProducts($category: String, $minPrice: Float, $maxPrice: Float) {
  products(category: $category, priceRange: {min: $minPrice, max: $maxPrice}) {
    id
    name
    slug
    price
    currency
    images {
      url
      alt
    }
    variants {
      sku
      size
      color
      stock
    }
  }
}
```

Создание заказа:
```graphql
mutation CreateOrder($input: OrderInput!) {
  createOrder(input: $input) {
    orderId
    status
    total
    currency
  }
}
```

**Introspection:**
- Включите для разработки
- Отключите в production или ограничьте доступ

**Rate Limiting:**
```json
{
  "enabled": true,
  "max_requests_per_minute": 100,
  "max_query_depth": 10,
  "max_query_complexity": 1000
}
```

**Schema Explorer:**

Перейдите в `Инструменты → GraphQL Playground` для:
- Исследования схемы
- Тестирования запросов
- Просмотра документации
- Генерации клиентов

---

## Управление медиа (DAM)

### Загрузка и организация

**Загрузка файлов:**

1. Перейдите в `Медиа → Библиотека`
2. Перетащите файлы или нажмите "Загрузить"
3. Максимальный размер настраивается в `Настройки → Медиа`

**Организация:**
- Папки и подпапки
- Теги для категоризации
- Поиск по названию, тегам, метаданным
- Фильтры по типу, дате, размеру

### Smart Cropping

**Включение smart cropping:**

1. Перейдите в `Настройки → Медиа → Smart Cropping`
2. Включите функцию
3. Выберите провайдера:
   - Cloudinary AI
   - Imgix Face Detection
   - Local TensorFlow.js

**Настройки:**
```json
{
  "enabled": true,
  "provider": "cloudinary",
  "focus_points": ["face", "object", "center"],
  "min_face_size": 50,
  "auto_crop_thumbnails": true
}
```

**Применение:**
При создании кропов система автоматически определяет важные области и сохраняет их в фокусе.

### Video Transcoding в HLS

**Настройка транскодинга:**

1. Перейдите в `Настройки → Медиа → Video`
2. Включите HLS транскодинг
3. Настройте параметры

**Параметры:**
```json
{
  "hls_enabled": true,
  "qualities": [
    {"name": "360p", "bitrate": "800k", "resolution": "640x360"},
    {"name": "480p", "bitrate": "1400k", "resolution": "854x480"},
    {"name": "720p", "bitrate": "2800k", "resolution": "1280x720"},
    {"name": "1080p", "bitrate": "5000k", "resolution": "1920x1080"}
  ],
  "audio_bitrate": "128k",
  "segment_duration": 6,
  "player": "videojs"
}
```

**Автоматическая обработка:**
При загрузке видео автоматически:
- Создается HLS manifest (.m3u8)
- Генерируются сегменты (.ts)
- Создается poster image
- Извлекаются субтитры (если есть)

**Плеер:**
Адаптивный HTML5 плеер с:
- Auto-quality switching
- Subtitle support
- Playback speed control
- Picture-in-picture

### Watermarking

**Настройка водяных знаков:**

1. Перейдите в `Настройки → Медиа → Watermark`
2. Загрузите логотип или создайте текстовый watermark
3. Настройте позицию и прозрачность

**Настройки:**
```json
{
  "enabled": true,
  "type": "image",
  "image": "/watermarks/logo.png",
  "text": "© Your Company",
  "position": "bottom-right",
  "offset_x": 20,
  "offset_y": 20,
  "opacity": 0.7,
  "min_image_width": 800,
  "apply_to_thumbnails": false
}
```

**Правила применения:**
- Применять к изображениям больше N пикселей
- Применять только к определенным папкам
- Не применять к thumbnail

### Face/Object Detection для авто-тегирования

**Включение авто-тегирования:**

1. Перейдите в `Настройки → Медиа → AI Tagging`
2. Включите функцию
3. Выберите провайдера:
   - Google Cloud Vision
   - AWS Rekognition
   - Azure Computer Vision
   - Local TensorFlow

**Настройки:**
```json
{
  "enabled": true,
  "provider": "google_vision",
  "confidence_threshold": 0.7,
  "max_tags": 20,
  "detect_faces": true,
  "detect_objects": true,
  "detect_text": true,
  "detect_landmarks": false
}
```

**Автоматические теги:**
При загрузке изображения система:
- Анализирует содержимое
- Добавляет теги (лица, объекты, цвета, сцены)
- Предлагает теги для утверждения
- Сохраняет метаданные

### CDN интеграции

**Поддерживаемые CDN:**
- Cloudflare
- Amazon CloudFront
- KeyCDN
- BunnyCDN
- Fastly

**Настройка CloudFront:**
```json
{
  "enabled": true,
  "distribution_id": "E1234567890ABC",
  "domain": "cdn.yourdomain.com",
  "origin": "s3://your-bucket/media",
  "signed_urls": false,
  "geo_restriction": "none"
}
```

**Инвалидация кэша:**
- Автоматическая при обновлении файла
- Ручная через UI
- По расписанию

---

## E-commerce функциональность

### Типы контента "Товар"

**Создание типа товара:**

1. Перейдите в `E-commerce → Товары → Типы товаров`
2. Нажмите "Создать тип"
3. Настройте поля:

**Стандартные поля:**
- Название
- Описание (rich text)
- SKU (артикул)
- Цена базовая
- Цена со скидкой
- Валюта
- Наличие (stock quantity)
- Категория
- Бренд
- Теги
- Изображения (галерея)

**Варианты товаров:**

Для товаров с вариантами (размер, цвет):
1. Создайте атрибуты: Размер, Цвет
2. Значения атрибутов: S/M/L/XL, Красный/Синий/Зеленый
3. Сгенерируйте комбинации вариантов
4. Для каждого варианта укажите:
   - SKU
   - Цену (может отличаться)
   - Наличие
   - Изображение (опционально)

**Пример настройки варианта:**
```json
{
  "product_id": 123,
  "attributes": {
    "size": "M",
    "color": "Red"
  },
  "sku": "TSHIRT-M-RED",
  "price": 29.99,
  "compare_at_price": 39.99,
  "stock": 150,
  "weight": 0.2,
  "barcode": "1234567890123"
}
```

### Корзина и чекаут

**Настройка корзины:**

1. Перейдите в `E-commerce → Настройки → Корзина`
2. Выберите тип хранения:
   - Session-based (гостям)
   - Database (зарегистрированным)
   - Hybrid

**Настройки:**
```json
{
  "guest_checkout": true,
  "cart_expiry_days": 30,
  "max_quantity_per_item": 99,
  "auto_add_insurance": false,
  "show_shipping_estimator": true,
  "save_for_later": true
}
```

**Чекаут процесс:**

**Шаги чекаута:**
1. Контактная информация
2. Адрес доставки
3. Способ доставки
4. Способ оплаты
5. Подтверждение

**Настройка полей:**
- Обязательные/необязательные
- Валидация
- Автозаполнение

**Гостевой чекаут:**
- Разрешить покупки без регистрации
- Предложить создать аккаунт после заказа
- Сохранить данные для будущих покупок

### Платежные шлюзы

**Поддерживаемые шлюзы:**
- Stripe
- PayPal
- Square
- Authorize.net
- ЮKassa
- Robokassa
- CloudPayments

**Настройка Stripe:**

1. Перейдите в `E-commerce → Настройки → Платежи`
2. Выберите Stripe
3. Введите credentials:

```json
{
  "enabled": true,
  "mode": "live",
  "publishable_key": "pk_live_xxx",
  "secret_key": "sk_live_xxx",
  "webhook_secret": "whsec_xxx",
  "supported_cards": ["visa", "mastercard", "amex"],
  "apple_pay": true,
  "google_pay": true,
  "saved_cards": true,
  "3d_secure": "automatic"
}
```

**Настройка PayPal:**
```json
{
  "enabled": true,
  "mode": "live",
  "client_id": "xxx",
  "client_secret": "xxx",
  "intent": "capture",
  "locale": "ru_RU"
}
```

**Webhooks:**
Настройте webhook endpoints для:
- Подтверждения платежа
- Возвратов
- Chargeback уведомлений
- Обновления статуса подписки

### Система заказов

**Просмотр заказов:**

Перейдите в `E-commerce → Заказы`

**Статусы заказов:**
- Pending (ожидает оплаты)
- Paid (оплачен)
- Processing (обрабатывается)
- Shipped (отправлен)
- Delivered (доставлен)
- Cancelled (отменен)
- Refunded (возвращен)

**Фильтры:**
- По статусу
- По дате
- По сумме
- По клиенту
- По способу оплаты

**Детали заказа:**
- Информация о клиенте
- Адреса доставки и биллинга
- Список товаров
- Примененные скидки
- Доставка
- Налоги
- История статусов
- Трек-номер доставки

**Управление заказами:**
- Изменение статуса
- Отправка уведомлений клиенту
- Печать накладных
- Создание возвратов
- Частичные возвраты

**Уведомления:**
Автоматические email/SMS при:
- Создании заказа
- Подтверждении оплаты
- Отправке заказа
- Доставке
- Возврате

**Экспорт:**
- Экспорт заказов в CSV/Excel
- Интеграция с CRM
- Синхронизация с 1С

---

## Аналитика и BI

### Кастомные дашборды

**Создание дашборда:**

1. Перейдите в `Аналитика → Дашборды`
2. Нажмите "Создать дашборд"
3. Добавьте виджеты

**Типы виджетов:**
- Графики (line, bar, pie, area)
- Таблицы
- KPI карточки
- Heatmaps
- Гео-карты
- Воронки

**Пример дашборда "Продажи":**
```json
{
  "name": "Продажи за месяц",
  "widgets": [
    {
      "type": "kpi",
      "title": "Общая выручка",
      "metric": "revenue.total",
      "period": "month"
    },
    {
      "type": "line_chart",
      "title": "Динамика продаж",
      "metric": "revenue.daily",
      "period": "30_days"
    },
    {
      "type": "pie_chart",
      "title": "Продажи по категориям",
      "metric": "revenue.by_category"
    },
    {
      "type": "table",
      "title": "Топ товаров",
      "metric": "products.top_sellers",
      "limit": 10
    }
  ]
}
```

### Синхронизация с Looker Studio/PowerBI

**Настройка экспорта:**

1. Перейдите в `Аналитика → Интеграции`
2. Выберите сервис
3. Настройте подключение

**Looker Studio:**
```json
{
  "enabled": true,
  "data_source_type": "google_sheets",
  "auto_sync": true,
  "sync_frequency": "daily",
  "tables": ["orders", "products", "customers"]
}
```

**PowerBI:**
```json
{
  "enabled": true,
  "connection_type": "direct_query",
  "endpoint": "https://api.yourdomain.com/bi/powerbi",
  "authentication": "api_key",
  "refresh_schedule": "0 6 * * *"
}
```

**REST API для BI:**
Endpoint: `GET /api/v1/analytics/export`

Параметры:
- metrics (список метрик)
- dimensions (группировка)
- filters (условия)
- date_from, date_to
- format (json, csv)

### Воронки продаж

**Создание воронки:**

1. Перейдите в `Аналитика → Воронки`
2. Нажмите "Создать воронку"
3. Определите этапы

**Пример воронки E-commerce:**
```
Этап 1: Посещение сайта
Этап 2: Просмотр товара
Этап 3: Добавление в корзину
Этап 4: Начало оформления
Этап 5: Завершенная покупка
```

**Настройка этапов:**
```json
{
  "name": "E-commerce Funnel",
  "steps": [
    {"name": "Page View", "event": "pageview", "path": "/"},
    {"name": "Product View", "event": "view_product", "path": "/product/*"},
    {"name": "Add to Cart", "event": "add_to_cart"},
    {"name": "Checkout Start", "event": "begin_checkout"},
    {"name": "Purchase", "event": "purchase"}
  ],
  "attribution_window": 30,
  "conversion_window": "days"
}
```

**Метрики воронки:**
- Конверсия между этапами
- Drop-off rate
- Среднее время между этапами
- Сегментация по источникам трафика

### Heatmaps

**Интеграция с heatmap сервисами:**

1. Перейдите в `Аналитика → Heatmaps`
2. Выберите сервис:
   - Hotjar
   - Crazy Egg
   - Microsoft Clarity
   - Mouseflow

**Настройка Hotjar:**
```json
{
  "enabled": true,
  "tracking_code": "hjxxxxxx",
  "record_pages": ["all"],
  "exclude_pages": ["/admin/*", "/checkout/*"],
  "sample_rate": 0.1,
  "record_duration": 180
}
```

**Типы heatmaps:**
- Click maps (клики)
- Move maps (движение мыши)
- Scroll maps (прокрутка)
- Attention maps (внимание)

**Session recordings:**
- Запись сессий пользователей
- Фильтры по событиям
- Аннотации и комментарии
- Поделиться с командой

---

## Логирование и мониторинг

### Sentry интеграция

**Настройка Sentry:**

1. Перейдите в `Настройки → Мониторинг → Sentry`
2. Создайте проект в Sentry.io
3. Введите DSN

```json
{
  "enabled": true,
  "dsn": "https://xxx@sentry.io/123456",
  "environment": "production",
  "release": "1.0.0",
  "error_types": ["error", "warning", "critical"],
  "sample_rate": 1.0,
  "traces_sample_rate": 0.1,
  "breadcrumbs": true,
  "user_context": true
}
```

**Отслеживаемые события:**
- PHP errors и exceptions
- JavaScript errors
- Failed API requests
- Slow database queries
- Memory issues

**Дашборд Sentry в админке:**
- Последние ошибки
- Top errors по частоте
- Распределение по типам
- Статус здоровья приложения

### Debugbar toggle

**Настройка Debugbar:**

1. Перейдите в `Настройки → Разработка → Debugbar`
2. Включите для определенных ролей/IP

```json
{
  "enabled": false,
  "enabled_for_roles": ["developer", "admin"],
  "enabled_for_ips": ["192.168.1.0/24"],
  "collectors": [
    "queries",
    "models",
    "mail",
    "logs",
    "route",
    "auth",
    "session",
    "request",
    "views",
    "cache",
    "time"
  ],
  "storage": "database",
  "max_entries": 50
}
```

**Toggle в UI:**
- Кнопка включения/выключения в админке
- Горячая клавиша (Ctrl+Shift+D)
- Автоматическое отключение в production

### Логи системы

**Просмотр логов:**

Перейдите в `Инструменты → Логи`

**Типы логов:**
- Application logs
- Error logs
- Access logs
- Queue logs
- Email logs

**Функции:**
- Поиск по тексту
- Фильтр по уровню (debug, info, warning, error, critical)
- Фильтр по дате
- Скачивание логов
- Очистка старых логов

**Логирование в разные каналы:**
```json
{
  "channels": {
    "stack": ["single", "slack"],
    "single": {
      "driver": "daily",
      "path": "storage/logs/laravel.log",
      "level": "debug",
      "days": 14
    },
    "slack": {
      "driver": "slack",
      "url": "https://hooks.slack.com/services/xxx",
      "level": "critical"
    }
  }
}
```

---

## Социальные интеграции и уведомления

### OG tags и соцсети

**Настройка Open Graph:**

1. Перейдите в `Маркетинг → Соцсети → Open Graph`
2. Настройте default values

**Default OG tags:**
```json
{
  "default_title": "Название сайта",
  "default_description": "Описание сайта",
  "default_image": "/images/og-default.jpg",
  "default_image_width": 1200,
  "default_image_height": 630,
  "site_name": "Название сайта",
  "locale": "ru_RU",
  "twitter_card": "summary_large_image",
  "twitter_site": "@yoursite"
}
```

**OG tags для страниц:**
Автоматически генерируются из:
- Заголовка страницы
- Meta description
- Featured image
- Автора (для статей)

**Предпросмотр:**
Для каждой страницы доступен предпросмотр:
- Facebook share preview
- Twitter card preview
- LinkedIn preview
- Telegram preview

### Уведомления в Slack

**Настройка Slack:**

1. Перейдите в `Настройки → Уведомления → Slack`
2. Создайте Incoming Webhook в Slack
3. Введите webhook URL

```json
{
  "enabled": true,
  "webhook_url": "https://hooks.slack.com/services/T00/B00/xxx",
  "channel": "#cms-alerts",
  "username": "CMS Bot",
  "icon_emoji": ":robot_face:",
  "notifications": {
    "new_order": true,
    "low_stock": true,
    "failed_queue": true,
    "security_alert": true,
    "system_error": true,
    "backup_completed": false
  }
}
```

**Формат сообщений:**
```
🛒 Новый заказ #12345
Сумма: $150.00
Клиент: John Doe
Статус: Ожидает оплаты
```

### Уведомления в Discord

**Настройка Discord:**

1. Перейдите в `Настройки → Уведомления → Discord`
2. Создайте Webhook в Discord канале
3. Введите webhook URL

```json
{
  "enabled": true,
  "webhook_url": "https://discord.com/api/webhooks/xxx",
  "username": "CMS Monitor",
  "avatar_url": "/images/bot-avatar.png",
  "embed_color": "5814783",
  "notifications": {
    "new_order": true,
    "system_error": true,
    "security_alert": true
  }
}
```

### Интеграция с Telegram

**Настройка Telegram бота:**

1. Перейдите в `Настройки → Интеграции → Telegram`
2. Создайте бота через @BotFather
3. Получите токен
4. Узнайте chat_id

```json
{
  "enabled": true,
  "bot_token": "123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11",
  "chat_id": "-1001234567890",
  "notifications": {
    "new_order": true,
    "new_comment": true,
    "form_submission": true,
    "system_alert": true
  }
}
```

**Команды бота:**
- `/status` - статус системы
- `/orders` - последние заказы
- `/visitors` - текущие посетители
- `/help` - список команд

### Интеграция с n8n

**Настройка webhook для n8n:**

1. Перейдите в `Настройки → Интеграции → Webhooks`
2. Создайте новый webhook
3. Скопируйте URL для n8n

**Пример workflow в n8n:**

```
Trigger: Webhook (POST /api/webhook/new-order)
↓
Filter: Order total > 100
↓
Action: Send email to sales team
↓
Action: Add to CRM
↓
Action: Send Slack notification
```

**Доступные webhook события:**
- order.created
- order.updated
- order.completed
- product.created
- product.updated
- user.registered
- form.submitted
- comment.posted

**Payload пример:**
```json
{
  "event": "order.created",
  "timestamp": "2024-01-15T10:30:00Z",
  "data": {
    "order_id": 12345,
    "customer": {...},
    "items": [...],
    "total": 150.00
  }
}
```

---

## AI и автоматизация

### AI Site Wizard - Мастер создания сайта

**AI Site Wizard** - это опциональный мастер первичной настройки, который помогает быстро создать структуру сайта с помощью AI. После установки CMS на хостинг, пользователи могут избежать проблемы "чистого листа" - AI сгенерирует:

- Название сайта и слоган
- Полную структуру страниц с контентом
- Навигационное меню
- Семантическое ядро (ключевые слова)
- План статей для блога/разделов
- Полный текст статей
- Промпты и сгенерированные изображения

**Запуск мастера:**

После первой установки CMS, при входе в админ-панель появится приветственное окно с предложением запустить AI Site Wizard.

```
┌─────────────────────────────────────────┐
│  🎉 Добро пожаловать в Vertex CMS!     │
│                                         │
│  Позвольте AI помочь вам создать сайт  │
│  за минуты вместо часов.               │
│                                         │
│  [Пропустить мастер]  [Начать]         │
└─────────────────────────────────────────┘
```

#### Шаг 1: Выбор AI провайдера

```
┌─────────────────────────────────────────┐
│  Выберите AI провайдера                 │
│                                         │
│  ○ OpenAI (GPT-4o, DALL-E 3)           │
│    Статус: ✓ Настроен                  │
│                                         │
│  ○ Anthropic (Claude Sonnet)           │
│    Статус: ⚠ Не настроен               │
│                                         │
│  ○ Grok (xAI)                          │
│    Статус: ⚠ Не настроен               │
│                                         │
│  ○ Custom Provider                     │
│    Статус: ⚠ Не настроен               │
│                                         │
│  [Настроить ключи]  [Продолжить]       │
└─────────────────────────────────────────┘
```

**Настройка API ключей:**

1. Перейдите в `Настройки → AI → API Keys`
2. Выберите провайдера
3. Введите API ключ:
   - **OpenAI**: `sk-...` (получить на [platform.openai.com](https://platform.openai.com))
   - **Anthropic**: `sk-ant-...` (получить на [console.anthropic.com](https://console.anthropic.com))
   - **Grok/xAI**: ключ из [console.x.ai](https://console.x.ai)
   - **Custom**: URL базового endpoint и ключ

#### Шаг 2: Описание вашего сайта

```
┌─────────────────────────────────────────┐
│  Расскажите о вашем сайте              │
│                                         │
│  О чем будет ваш сайт?                 │
│  ┌───────────────────────────────────┐ │
│  │ Я открываю небольшую кофейню      │ │
│  │ под названием \"Morning Brew\" в   │ │
│  │ Сиэтле. Мы специализируемся на   │ │
│  │ авторском кофе и домашней        │ │
│  │ выпечке...                        │ │
│  └───────────────────────────────────┘ │
│                                         │
│  Ниша/Отрасль: [Еда и напитки ▼]      │
│  Целевая аудитория: [Местные жители,  │
│                       ценители кофе]   │
│  Тон голоса: [Дружелюбный ▼]          │
│  Язык: [Русский ▼]                    │
│                                         │
│  [Назад]  [Сгенерировать структуру]   │
└─────────────────────────────────────────┘
```

#### Шаг 3: Генерация структуры сайта

Во время генерации (30-90 секунд):

```
┌─────────────────────────────────────────┐
│  ✨ AI создает ваш сайт...             │
│                                         │
│  ━━━━━━━━━━━━━━━━░░░░  65%             │
│                                         │
│  • Анализ требований... ✓              │
│  • Создание структуры сайта... ✓       │
│  • Написание контента страниц... ⏳    │
│  • Построение навигационного меню... ⏳│
│  • Генерация SEO данных... ⏳          │
│                                         │
│  Это может занять до 2 минут           │
└─────────────────────────────────────────┘
```

**Результат генерации:**

```
┌─────────────────────────────────────────┐
│  Название сайта: \"Morning Brew\"        │
│  Слоган: \"Авторский кофе и выпечка\"    │
│                                         │
│  Сгенерировано страниц (5):            │
│  ┌─────────────────────────────────┐   │
│  │ ☑ Главная                       │   │
│  │ ☑ О нас                         │   │
│  │ ☑ Меню                          │   │
│  │ ☑ Наша история                  │   │
│  │ ☑ Контакты                      │   │
│  └─────────────────────────────────┘   │
│                                         │
│  Пункты меню:                          │
│  Главная | Меню | О нас | Контакты     │
│                                         │
│  [Перегенерировать]  [Редактировать]  │
│  [Продолжить]                          │
└─────────────────────────────────────────┘
```

#### Шаг 4: Семантическое ядро (опционально)

```
┌─────────────────────────────────────────┐
│  📊 Генерация семантического ядра     │
│                                         │
│  Сгенерировать ключевые слова для SEO? │
│                                         │
│  Будет создано:                        │
│  • Основные ключевые слова (10-15)     │
│  • Длинные запросы (20-30)             │
│  • Вопросительные запросы (10-15)      │
│  • Коммерческие ключевые слова (10-15) │
│                                         │
│  [Пропустить]  [Сгенерировать]         │
└─────────────────────────────────────────┘
```

**Пример результатов:**

```
┌─────────────────────────────────────────┐
│  Сгенерировано ключевых слов: 87       │
│                                         │
│  Основные ключевые слова:              │
│  • кофе Сиэтл                          │
│  • кофейня рядом                        │
│  • авторский кофе                       │
│  • свежая выпечка                       │
│  ...                                   │
│                                         │
│  [Экспорт CSV]  [Добавить в SEO]       │
│  [Пропустить]                          │
└─────────────────────────────────────────┘
```

#### Шаг 5: План контента для блога/разделов

```
┌─────────────────────────────────────────┐
│  📝 Генерация контент-плана           │
│                                         │
│  Раздел: Блог                          │
│  Тематика: Кофейная культура и советы  │
│                                         │
│  Сколько статей запланировать?         │
│  [5 ▼]                                 │
│                                         │
│  Сгенерированные статьи:               │
│  ┌─────────────────────────────────┐   │
│  │ 1. \"Как выбрать правильный кофе\" │   │
│  │    Приоритет: Высокий           │   │
│  │    Объем: ~1500 слов            │   │
│  │                                 │   │
│  │ 2. \"5 способов заваривания...\"  │   │
│  │    Приоритет: Средний           │   │
│  │    Объем: ~2000 слов            │   │
│  │    ...                          │   │
│  └─────────────────────────────────┘   │
│                                         │
│  [Перегенерировать]  [Выбрать все]    │
│  [Далее]                               │
└─────────────────────────────────────────┘
```

#### Шаг 6: Генерация контента статей (опционально)

```
┌─────────────────────────────────────────┐
│  ✍️ Написание статей                   │
│                                         │
│  Выбрано: \"Как выбрать правильный кофе\" │
│                                         │
│  План статьи:                          │
│  • Введение                            │
│  • Виды кофейных зерен                 │
│  • Степени обжарки                     │
│  • Как хранить кофе                    │
│  • Заключение                          │
│                                         │
│  [ ] Сгенерировать полный текст        │
│      (~1500 слов)                      │
│  [ ] Сгенерировать главное изображение │
│                                         │
│  [Пропустить]  [Сгенерировать]         │
└─────────────────────────────────────────┘
```

#### Шаг 7: Генерация изображений (опционально)

**Поддерживаемые провайдеры для генерации изображений:**
- **DALL-E 3** (OpenAI) - высокое качество, детализация
- **Grok** (xAI) - быстрая генерация, креативность
- **ChatGPT Image** - интеграция через OpenAI API
- **Stable Diffusion** (через Custom API)

```
┌─────────────────────────────────────────┐
│  🎨 Генерация изображений             │
│                                         │
│  Для: Главное изображение главной      │
│                                         │
│  Промпт (AI сгенерировал):             │
│  \"Уютный интерьер кофейни с теплым     │
│  освещением, деревянные столы, бариста │
│  готовит эспрессо, пар поднимается от  │
│  чашек, приглашающая атмосфера,        │
│  профессиональная фотография, высокое  │
│  разрешение\"                          │
│                                         │
│  Настройки:                            │
│  Размер: [1024x1024 ▼]                │
│  Качество: [Стандартное ▼]            │
│  Вариантов: [1 ▼]                     │
│                                         │
│  Стоимость: ~$0.04 за изображение     │
│                                         │
│  [Редактировать промпт]  [Генерировать]│
└─────────────────────────────────────────┘
```

**Конфигурация генерации изображений:**

```json
{
  \"provider\": \"openai\",
  \"model\": \"dall-e-3\",
  \"size\": \"1024x1024\",
  \"quality\": \"standard\",
  \"style\": \"natural\",
  \"count\": 1,
  \"auto_save_to_media\": true
}
```

#### Шаг 8: Финальный обзор и сохранение

```
┌─────────────────────────────────────────┐
│  ✅ Готово к созданию сайта!          │
│                                         │
│  Итого:                                │
│  • Название сайта: Morning Brew        │
│  • Страниц: 5                          │
│  • Пунктов меню: 4                     │
│  • Ключевых слов: 87                   │
│  • Планов статей: 5                    │
│  • Написано статей: 2                  │
│  • Сгенерировано изображений: 3        │
│                                         │
│  Весь контент будет сохранен как       │
│  черновик. Вы сможете всё              │
│  отредактировать позже.                │
│                                         │
│  [Назад]  [Создать сайт]               │
└─────────────────────────────────────────┘
```

**API Endpoints для разработчиков:**

Для интеграции с фронтендом используются следующие endpoints:

```bash
# Генерация структуры сайта
POST /api/ai/wizard/generate-structure

# Генерация семантического ядра
POST /api/ai/wizard/generate-semantic-core

# Генерация плана статей
POST /api/ai/wizard/generate-article-plan

# Генерация контента статьи
POST /api/ai/wizard/generate-article-content

# Генерация промпта для изображения
POST /api/ai/wizard/generate-image-prompt

# Генерация изображения
POST /api/ai/wizard/generate-image

# Сохранение структуры в БД
POST /api/ai/wizard/save-structure
```

**Пример запроса для генерации структуры:**

```bash
curl -X POST https://your-cms.com/api/ai/wizard/generate-structure \
  -H \"Authorization: Bearer YOUR_TOKEN\" \
  -H \"Content-Type: application/json\" \
  -d '{
    \"provider\": \"openai\",
    \"description\": \"Интернет-магазин экологичных товаров для дома\",\
    \"niche\": \"E-commerce, Eco-friendly products\",\
    \"target_audience\": \"Эко-сознательные потребители 25-45 лет\",\
    \"tone\": \"friendly, informative\",\
    \"language\": \"ru\"
  }'
```

**Конфигурация через Admin Panel:**

1. Перейдите в `Настройки → AI → Site Wizard`
2. Включите/выключите мастер
3. Настройте AI провайдера по умолчанию
4. Установите лимиты токенов
5. Настройте авто-сохранение черновиков

```json
{
  \"enabled\": true,
  \"default_provider\": \"openai\",
  \"default_model\": \"gpt-4o-mini\",
  \"image_provider\": \"openai\",
  \"image_model\": \"dall-e-3\",
  \"token_limits\": {
    \"structure\": 4000,
    \"article\": 2000,
    \"semantic_core\": 3000
  },
  \"auto_save_drafts\": true,
  \"show_cost_estimates\": true
}
```

**Подробная документация:** См. [`/docs/AI_SITE_WIZARD.md`](/docs/AI_SITE_WIZARD.md)

---

### RAG Консультант (AI Chat Bot)

**Настройка AI консультанта:**

1. Перейдите в `SEO → AI Knowledge Base → Настройки`
2. Настройте API ключи:
   - **OpenAI API Key**: получите на [platform.openai.com](https://platform.openai.com)
   - **Supabase URL**: URL вашего проекта Supabase
   - **Supabase API Key**: anon/public ключ из настроек Supabase

**Конфигурация моделей:**

```json
{
  "embedding_model": "text-embedding-ada-002",
  "chat_model": "gpt-3.5-turbo",
  "temperature": 0.3,
  "max_chunks": 5,
  "min_similarity": 30,
  "chunk_size": 500
}
```

**Управление базой знаний:**

1. Перейдите в `SEO → AI Knowledge Base`
2. Создайте категории для документов
3. Добавьте документы (текст или файлы)
4. Дождитесь автоматической обработки (разбиение на чанки, генерация эмбеддингов)

**Установка виджета на сайт:**

Добавьте в шаблон перед `</body>`:

```blade
<x-ai-chat.widget 
    title="Онлайн-помощник"
    color="#4f46e5"
    position="right"
/>
```

**Мониторинг:**

- История чатов: `SEO → AI Knowledge Base → История чатов`
- Логи: `storage/logs/laravel.log`
- Статистика: главная страница AI Knowledge Base

**Подробная документация:** См. [`/docs/RAG_MODULE_ADMIN_GUIDE.md`](/docs/RAG_MODULE_ADMIN_GUIDE.md)

---

### Авто-заполнение контента

**Настройка AI помощника:**

1. Перейдите в `Настройки → AI → Content Assistant`
2. Выберите провайдера:
   - OpenAI GPT-4
   - Anthropic Claude
   - Google Gemini
   - Local LLM

**Настройка OpenAI:**
```json
{
  "enabled": true,
  "provider": "openai",
  "api_key": "sk-xxx",
  "model": "gpt-4",
  "temperature": 0.7,
  "max_tokens": 2000,
  "features": {
    "generate_titles": true,
    "generate_descriptions": true,
    "generate_content": true,
    "summarize": true,
    "translate": true,
    "improve_writing": true
  }
}
```

**Использование:**
В редакторе контента:
- Кнопка "Generate with AI"
- Выбор типа генерации
- Предпросмотр результата
- Редактирование перед сохранением

### AI чат-бот

**Настройка чат-бота:**

1. Перейдите в `Инструменты → Chatbot`
2. Включите чат-бот
3. Настройте поведение

**Конфигурация:**
```json
{
  "enabled": true,
  "provider": "openai",
  "model": "gpt-4",
  "knowledge_base": {
    "sources": ["faq", "documentation", "product_catalog"],
    "auto_sync": true
  },
  "personality": {
    "tone": "friendly",
    "language": "ru",
    "max_response_length": 500
  },
  "handoff": {
    "enabled": true,
    "trigger_keywords": ["оператор", "помощь", "человек"],
    "notify_email": "support@company.com"
  },
  "analytics": true
}
```

**Виджет на сайте:**
- Плавающая кнопка в углу
- Кастомизируемый дизайн
- История чата
- Оценка ответов

### Умный поиск

**Настройка умного поиска:**

1. Перейдите в `Настройки → Поиск → Smart Search`
2. Включите AI-enhanced search

**Функции:**
- Semantic search (понимание смысла)
- Autocomplete с подсказками
- Spell correction
- Synonym matching
- Personalized results
- Voice search

**Конфигурация:**
```json
{
  "enabled": true,
  "engine": "elasticsearch",
  "ai_enhanced": true,
  "features": {
    "autocomplete": true,
    "spell_check": true,
    "synonyms": true,
    "facets": true,
    "highlighting": true,
    "personalization": true
  },
  "ranking": {
    "relevance": 0.5,
    "popularity": 0.3,
    "recency": 0.2
  }
}
```

---

## Архитектурные улучшения

### Система хуков через UI

**Управление хуками:**

1. Перейдите в `Разработка → Хуки`
2. Просмотр доступных хуков
3. Создание кастомных обработчиков

**Типы хуков:**
- Action hooks (события)
- Filter hooks (модификация данных)

**Пример создания хука:**
```json
{
  "hook_name": "after_order_created",
  "type": "action",
  "priority": 10,
  "callback_type": "custom_code",
  "code": "Log::info('Order created: ' . $order->id);",
  "active": true
}
```

**Встроенные хуки:**
- before_content_save
- after_content_save
- before_user_login
- after_user_register
- before_payment_process
- after_order_status_change

### Webhooks менеджер

**Создание webhook:**

1. Перейдите в `Инструменты → Webhooks`
2. Нажмите "Добавить webhook"
3. Настройте параметры

**Конфигурация:**
```json
{
  "name": "Notify CRM about new orders",
  "url": "https://crm.example.com/api/webhook",
  "method": "POST",
  "events": ["order.created", "order.updated"],
  "headers": {
    "Authorization": "Bearer xxx",
    "Content-Type": "application/json"
  },
  "retry_policy": {
    "max_attempts": 3,
    "backoff_seconds": 60
  },
  "signature": {
    "enabled": true,
    "algorithm": "sha256",
    "secret": "webhook_secret"
  },
  "active": true
}
```

**Логирование:**
- История отправок
- Статус ответов
- Payload и response
- Retry attempts

### CLI команды для разработчиков

**Доступные команды:**

```bash
# Создание супер-админа
php artisan make:superadmin

# Оптимизация для production
php artisan optimize

# Очистка кэша
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Очереди
php artisan queue:work
php artisan queue:restart
php artisan queue:failed
php artisan queue:retry all

# База данных
php artisan db:seed
php artisan db:wipe
php artisan db:backup

# Медиа
php artisan media:optimize
php artisan media:regenerate-thumbnails
php artisan media:cleanup

# Контент
php artisan content:export
php artisan content:import
php artisan sitemap:generate

# Безопасность
php artisan security:audit
php artisan security:fix-permissions

# Мониторинг
php artisan health:check
php artisan performance:test

# Разработка
php artisan make:module Name
php artisan make:hook Name
php artisan make:widget Name
```

---

## Надежность и Disaster Recovery

### Point-in-time Recovery

**Настройка бэкапов:**

1. Перейдите в `Настройки → Бэкапы`
2. Настройте расписание

**Конфигурация:**
```json
{
  "enabled": true,
  "database": {
    "driver": "mysql",
    "backup_frequency": "hourly",
    "retention_days": 30,
    "compression": true,
    "encryption": true
  },
  "files": {
    "directories": ["storage", "uploads"],
    "backup_frequency": "daily",
    "retention_days": 14
  },
  "destination": {
    "type": "s3",
    "bucket": "cms-backups",
    "region": "us-east-1",
    "encryption": "AES256"
  },
  "notifications": {
    "on_success": false,
    "on_failure": true
  }
}
```

**Point-in-time recovery:**
- Восстановление на любой момент времени
- Выбор конкретной версии бэкапа
- Предпросмотр перед восстановлением
- Восстановление отдельных таблиц

### Staging Environment

**Создание staging окружения:**

1. Перейдите в `Настройки → Окружения`
2. Нажмите "Создать staging"

**Процесс:**
- Клонирование production базы (с анонимизацией данных)
- Копирование файлов
- Изолированное окружение
- Отдельный домен: `staging.yoursite.com`

**Синхронизация:**
- Push from production to staging
- Pull changes from staging
- Scheduled sync (nightly)

**Различия:**
- Отключенные внешние сервисы (email, SMS)
- Test payment gateways
- Debug mode enabled

### Self-healing сервисов

**Настройка auto-healing:**

1. Перейдите в `Настройки → Мониторинг → Auto-healing`
2. Включите функции

**Автоматические действия:**
```json
{
  "enabled": true,
  "actions": {
    "restart_worker": {
      "trigger": "queue_stalled_minutes > 10",
      "action": "restart_workers"
    },
    "clear_cache": {
      "trigger": "memory_usage_percent > 90",
      "action": "clear_application_cache"
    },
    "scale_workers": {
      "trigger": "queue_size > 1000",
      "action": "increase_workers",
      "count": 2
    },
    "failover_database": {
      "trigger": "database_connection_failed",
      "action": "switch_to_replica"
    }
  },
  "notifications": {
    "on_action": true,
    "channel": "slack"
  }
}
```

---

## Экосистема и Marketplace

### Marketplace модулей

**Доступ к marketplace:**

1. Перейдите в `Администрирование → Marketplace`
2. Браузуйте доступные модули

**Категории модулей:**
- E-commerce расширения
- Интеграции (CRM, ERP, Marketing)
- SEO инструменты
- Аналитика
- Безопасность
- UI компоненты
- Payment gateways

**Установка модуля:**
1. Выберите модуль
2. Нажмите "Установить"
3. Примите условия лицензии
4. Дождитесь установки
5. Активируйте модуль

**Управление модулями:**
- Просмотр установленных
- Обновление
- Деактивация
- Удаление

### White Label

**Настройка white label:**

1. Перейдите в `Настройки → White Label`
2. Настройте брендинг

**Конфигурация:**
```json
{
  "enabled": true,
  "branding": {
    "app_name": "Your CMS",
    "logo": "/images/white-label-logo.png",
    "favicon": "/images/favicon.ico",
    "colors": {
      "primary": "#3490dc",
      "secondary": "#ffed4e"
    },
    "footer_text": "© 2024 Your Company"
  },
  "domain": {
    "custom_domain": "cms.yourcompany.com",
    "ssl_certificate": "auto"
  },
  "emails": {
    "from_name": "Your CMS",
    "from_email": "noreply@yourcompany.com",
    "template_customization": true
  }
}
```

### SaaS mode с биллингом

**Включение SaaS режима:**

1. Перейдите в `Настройки → SaaS`
2. Включите multi-tenant mode

**Конфигурация:**
```json
{
  "enabled": true,
  "tenancy": {
    "mode": "database",
    "central_domain": "platform.yoursite.com",
    "tenant_domains": true
  },
  "billing": {
    "provider": "stripe",
    "plans": [
      {
        "name": "Starter",
        "price": 29,
        "currency": "USD",
        "interval": "monthly",
        "features": {
          "max_users": 5,
          "max_storage_gb": 10,
          "max_bandwidth_gb": 100,
          "support": "email"
        }
      },
      {
        "name": "Professional",
        "price": 99,
        "currency": "USD",
        "interval": "monthly",
        "features": {
          "max_users": 25,
          "max_storage_gb": 100,
          "max_bandwidth_gb": 1000,
          "support": "priority"
        }
      },
      {
        "name": "Enterprise",
        "price": 499,
        "currency": "USD",
        "interval": "monthly",
        "features": {
          "max_users": -1,
          "max_storage_gb": -1,
          "max_bandwidth_gb": -1,
          "support": "dedicated"
        }
      }
    ],
    "trial_days": 14,
    "invoice_branding": true
  }
}
```

**Tenant management:**
- Создание новых tenants
- Мониторинг использования
- Billing и invoicing
- Suspension за неуплату
- Migration между планами

---

## Приложения

### A. Горячие клавиши

| Клавиши | Действие |
|---------|----------|
| Ctrl+S | Сохранить |
| Ctrl+Z | Отменить |
| Ctrl+Y | Вернуть |
| Ctrl+K | Быстрый поиск |
| Ctrl+Shift+D | Toggle Debugbar |
| ? | Показать все горячие клавиши |

### B. API Endpoints

**REST API:**
- Base URL: `https://yourdomain.com/api/v1`
- Authentication: Bearer token
- Rate limit: 100 requests/minute

**GraphQL:**
- Endpoint: `https://yourdomain.com/graphql`
- Introspection: Enabled for authenticated users

### C. Поддержка

**Контакты:**
- Email: support@cms.com
- Documentation: https://docs.cms.com
- Community Forum: https://community.cms.com
- GitHub: https://github.com/your-org/cms

### D. Changelog

Версия 1.0.0 (Январь 2024):
- Initial release
- All core features implemented
- Security hardening
- Performance optimizations

---

*Документация последний раз обновлена: Январь 2024*
*Версия CMS: 1.0.0*
