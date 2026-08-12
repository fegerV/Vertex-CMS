# Vertex CMS - Инструкция по установке

Для развёртывания локальной тестовой среды на Windows 10 используйте отдельное руководство: [`docs/testing-windows-10.md`](docs/testing-windows-10.md).

## 🚀 Установка на хостинг (как WordPress)

Vertex CMS спроектирована так, чтобы её установка была такой же простой, как у WordPress. Выберите удобный способ:

---

## Метод 1: Веб-мастер установки (рекомендуется)

Идеально подходит для shared-хостингов и пользователей без опыта работы с командной строкой.

### Шаг 1: Загрузка файлов

**Вариант A: Через FTP/SFTP**
1. Подключитесь к вашему хостингу через FTP-клиент (FileZilla, WinSCP)
2. Распакуйте архив `vertex-cms.zip` на вашем компьютере
3. Загрузите **все файлы** из распакованной папки в корень вашего сайта (обычно `public_html` или `www`)

**Вариант B: Через файловый менеджер хостинга**
1. Зайдите в панель управления хостингом (cPanel, ISPManager, Plesk)
2. Откройте "Файловый менеджер"
3. Перейдите в корневую директорию сайта
4. Загрузите архив `vertex-cms.zip`
5. Распакуйте архив прямо на хостинге

### Шаг 2: Настройка прав доступа

**Критически важный шаг!** Неправильные права приведут к ошибкам.

Установите следующие права на папки:

```bash
# Для Linux/VPS серверов
chmod -R 755 /path/to/your/site
chmod -R 777 /path/to/your/site/storage
chmod -R 777 /path/to/your/site/bootstrap/cache

# Установите владельца (если есть доступ к SSH)
chown -R www-data:www-data /path/to/your/site
# или
chown -R nobody:nobody /path/to/your/site
```

**Для хостингов без SSH:**
- Используйте файловый менеджер cPanel/ISPManager
- Кликните правой кнопкой на папку → "Change Permissions" / "Права доступа"
- Установите `777` для папок `storage` и `bootstrap/cache`
- Установите `755` для остальных папок

### Шаг 3: Создание базы данных

1. Зайдите в панель управления хостингом
2. Найдите раздел "Базы данных MySQL" / "MySQL Databases"
3. Создайте новую базу данных (например, `username_vertex`)
4. Создайте нового пользователя БД с надежным паролем
5. **Добавьте пользователя к базе данных** с правами **ALL PRIVILEGES** / "Все привилегии"

📝 **Запишите данные:**
- Имя базы данных: `________`
- Пользователь БД: `________`
- Пароль БД: `________`
- Хост БД: `________` (обычно `localhost` или `127.0.0.1`)

### Шаг 4: Запуск мастера установки

1. Откройте браузер
2. Перейдите по адресу: `https://ваш-сайт.com/install`
   - Если CMS установлена в подпапку: `https://ваш-сайт.com/папка/install`
3. Вы увидите мастер установки с 4 шагами:

#### Шаг 4.1: Приветствие
- Прочитайте требования к серверу
- Нажмите "Начать установку"

#### Шаг 4.2: Проверка требований
Система автоматически проверит:
- ✅ Версию PHP (требуется >= 8.1)
- ✅ Расширения PHP (mbstring, openssl, pdo, curl и др.)
- ✅ Права на запись в `storage` и `bootstrap/cache`
- ✅ Наличие модуля mod_rewrite (для Apache)

**Если есть ошибки:**
- Красным будут выделены недостающие компоненты
- Следуйте рекомендациям по исправлению
- Нажмите "Проверить снова" после исправлений

#### Шаг 4.3: Настройка базы данных

Заполните форму данными из Шага 3:

| Поле | Пример | Где взять |
|------|--------|-----------|
| Host | `localhost` | Обычно localhost |
| Database | `username_vertex` | Из шага 3 |
| Username | `username_user` | Из шага 3 |
| Password | `********` | Из шага 3 |
| Prefix | `vertex_` | Опционально |

Нажмите "Проверить подключение" → если успешно, нажмите "Продолжить".

#### Шаг 4.4: Создание администратора

Заполните данные первого пользователя (администратора):

- **Имя**: Ваше имя или название сайта
- **Email**: Ваш рабочий email (на него придет пароль)
- **Пароль**: Минимум 8 символов (используйте надежный пароль!)
- **Подтверждение пароля**: Повторите пароль

**Настройки сайта:**
- **Название сайта**: Например, "Мой интернет-магазин"
- **URL сайта**: Должен определиться автоматически
- **Часовой пояс**: Выберите ваш часовой пояс
- **Язык**: Русский / English

Нажмите "Установить CMS".

#### Шаг 4.5: Завершение

После успешной установки вы увидите:
- ✅ Сообщение об успешной установке
- 📝 Данные для входа (продублируются)
- 🔗 Ссылку на админ-панель

**Важно:** Файл `storage/installed.lock` будет создан автоматически. Он блокирует повторный запуск мастера установки в целях безопасности.

### Шаг 5: Первый вход

1. Перейдите в админ-панель: `https://ваш-сайт.com/admin`
2. Введите email и пароль администратора
3. Готово! CMS готова к работе.

### Шаг 6: Первоначальная настройка

Рекомендуется сразу настроить:

1. **Система → Настройки**
   - Общие настройки сайта
   - SEO параметры
   - Настройки почты

2. **Система → Бэкапы**
   - Настройте расписание авто-бэкапов
   - Создайте первый бэкап вручную

3. **Пользователи → Мой профиль**
   - Включите двухфакторную аутентификацию (2FA)
   - Обновите личные данные

---

## Метод 2: Установка через CLI (для VPS)

Идеально подходит для VPS/VDS серверов и опытных пользователей.

### Требования
- Доступ к SSH
- PHP 8.1+ установлен
- Composer установлен глобально
- База данных создана заранее

### Шаг 1: Клонирование проекта

```bash
# Перейдите в корень сайта
cd /var/www/your-site

# Клонируйте репозиторий (или распакуйте архив)
git clone https://github.com/vertexcms/core.git .

# Или загрузите архив
wget https://vertexcms.com/downloads/latest.zip
unzip latest.zip
rm latest.zip
```

### Шаг 2: Установка зависимостей

```bash
# Установка PHP зависимостей через Composer
composer install --no-dev --optimize-autoloader

# Если нужны dev-зависимости (для разработки)
composer install
```

### Шаг 3: Настройка окружения

```bash
# Копирование .env файла
cp .env.example .env

# Генерация ключа приложения
php artisan key:generate
```

### Шаг 4: Интерактивная установка

Запустите команду установки:

```bash
php artisan cms:install
```

Вас попросят ввести данные:

```
╔═══════════════════════════════════════════╗
║     Vertex CMS Installation Wizard        ║
╚═══════════════════════════════════════════╝

Database Configuration:
  Database driver (mysql): mysql
  Database host (127.0.0.1): localhost
  Database port (3306): 3306
  Database name: vertex_cms
  Database username: root
  Database password: [скрытый ввод]
  Table prefix (vertex_): 

Admin User:
  Name: Admin
  Email: admin@example.com
  Password: ********
  
Site Settings:
  Site name: My Website
  Site URL: https://example.com
  Timezone: Europe/Moscow
  Language: ru

✓ Database configured successfully
✓ Migrations executed
✓ Admin user created
✓ Settings saved
✓ Cache cleared

╔═══════════════════════════════════════════╗
║  Installation completed successfully!     ║
║                                           ║
║  Admin panel: https://example.com/admin   ║
║  Email: admin@example.com                 ║
╚═══════════════════════════════════════════╝
```

### Шаг 5: Оптимизация для production

```bash
# Очистка и кэширование конфигурации
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Установка правильных прав
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data .
```

### Шаг 6: Настройка веб-сервера

#### Nginx конфигурация

```nginx
server {
    listen 443 ssl http2;
    server_name example.com www.example.com;
    root /var/www/your-site/public;

    ssl_certificate /etc/letsencrypt/live/example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/example.com/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache конфигурация

Файл `.htaccess` уже включен в комплект поставки в папке `public/`.

Убедитесь, что включен модуль `mod_rewrite`:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Шаг 7: Настройка очередей (опционально)

Для работы фоновых задач настройте Supervisor:

```bash
# Установка Supervisor
sudo apt-get install supervisor

# Создание конфига
sudo nano /etc/supervisor/conf.d/vertex-worker.conf
```

Содержимое конфига:

```ini
[program:vertex-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/your-site/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasuser=false
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/your-site/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Применение конфигурации
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start vertex-worker:*
```

### Шаг 8: Настройка Cron

Для планировщика задач Laravel:

```bash
crontab -e
```

Добавьте строку:

```bash
* * * * * cd /var/www/your-site && php artisan schedule:run >> /dev/null 2>&1
```

---

## Метод 3: Docker (для разработки)

```bash
# Клонирование репозитория
git clone https://github.com/vertexcms/core.git
cd core

# Запуск через Docker Compose
docker-compose up -d

# Установка внутри контейнера
docker-compose exec app composer install
docker-compose exec app php artisan cms:install
```

---

## 🔧 Решение проблем при установке

### Ошибка: "Permission denied"

**Проблема:** Недостаточно прав на запись в папки.

**Решение:**
```bash
chmod -R 777 storage bootstrap/cache
chown -R www-data:www-data /path/to/site
```

Для хостингов используйте файловый менеджер.

### Ошибка: "Could not find driver"

**Проблема:** Не установлено расширение PDO для вашей БД.

**Решение:**
- Для MySQL: установите `php-pdo_mysql`
- Для PostgreSQL: установите `php-pdo_pgsql`

```bash
# Ubuntu/Debian
sudo apt-get install php8.1-mysql
sudo systemctl restart php8.1-fpm

# CentOS/RHEL
sudo yum install php-pdo
sudo systemctl restart php-fpm
```

На shared-хостингах выберите нужную версию PHP в панели управления.

### Ошибка: "Class 'ZipArchive' not found"

**Проблема:** Не установлено расширение ZIP.

**Решение:**
```bash
sudo apt-get install php8.1-zip
sudo systemctl restart php8.1-fpm
```

### Ошибка: "SQLSTATE[HY000] [2002] Connection refused"

**Проблема:** Неверные данные подключения к БД или БД не запущена.

**Решение:**
1. Проверьте credentials в `.env`
2. Убедитесь, что БД создана
3. Проверьте, что пользователь БД имеет права
4. Для localhost убедитесь, что MySQL запущен

### Белый экран после установки

**Проблема:** Ошибка PHP не отображается.

**Решение:**
1. Включите отображение ошибок в `.env`:
   ```
   APP_DEBUG=true
   ```
2. Проверьте логи: `storage/logs/laravel.log`
3. Очистите кэш:
   ```bash
   php artisan optimize:clear
   ```

### Мастер установки запускается повторно

**Проблема:** Файл `installed.lock` не создан или удален.

**Решение:**
```bash
# Создайте файл вручную
touch storage/installed.lock
chmod 644 storage/installed.lock
```

### Ошибка: "The requested URL was not found on this server"

**Проблема:** Не настроен URL rewriting.

**Решение для Apache:**
1. Убедитесь, что `.htaccess` существует в папке `public/`
2. Включите `mod_rewrite`:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
3. Разрешите override в конфиге Apache:
   ```apache
   <Directory /var/www/html>
       AllowOverride All
   </Directory>
   ```

**Решение для Nginx:**
Проверьте конфигурацию `try_files` (см. выше).

---

## 📞 Нужна помощь?

Если вы столкнулись с проблемой, которой нет в этом руководстве:

1. **Проверьте логи:** `storage/logs/laravel.log`
2. **Документация:** https://docs.vertexcms.com
3. **Форум:** https://forum.vertexcms.com
4. **Telegram чат:** https://t.me/vertexcms
5. **Support:** support@vertexcms.com

При обращении в поддержку приложите:
- Версию PHP
- Версию MySQL/PostgreSQL
- Тип хостинга (shared/VPS)
- Текст ошибки из логов
- Скриншот проблемы

---

**Поздравляем с успешной установкой Vertex CMS!** 🎉

Перейдите в админ-панель: `https://ваш-сайт.com/admin`
