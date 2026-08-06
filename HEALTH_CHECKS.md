# Мониторинг здоровья системы (Health Checks)

## Эндпоинты здоровья

### 1. Базовый health check
```
GET /up
```
Возвращает `200 OK` если приложение запущено.

### 2. Полный health check
```
GET /health
```
Проверяет:
- Подключение к базе данных
- Подключение к Redis
- Доступность файловой системы
- Наличие места на диске

Пример ответа:
```json
{
  "status": "healthy",
  "checks": {
    "database": "ok",
    "redis": "ok",
    "storage": "ok",
    "disk_space": "ok"
  },
  "timestamp": "2024-01-01T00:00:00Z"
}
```

### 3. Ready check (для Kubernetes)
```
GET /ready
```
Проверяет готовность приложения принимать трафик.

## Настройка для Docker

Добавьте в docker-compose.yml:
```yaml
healthcheck:
  test: ["CMD", "curl", "-f", "http://localhost/up"]
  interval: 30s
  timeout: 10s
  retries: 3
  start_period: 40s
```

## Настройка для Nginx

```nginx
location /up {
    access_log off;
    return 200 "OK\n";
    add_header Content-Type text/plain;
}
```

## Использование в CI/CD

```bash
# Проверка перед деплоем
curl -f http://localhost/up || exit 1

# Полная проверка здоровья
curl -f http://localhost/health | jq '.status' | grep -q healthy
```
