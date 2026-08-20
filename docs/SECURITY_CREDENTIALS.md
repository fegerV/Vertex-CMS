# Production Credentials Setup Guide

## Security Modules Configuration

### 1. WAF (Web Application Firewall)

Enable and configure WAF rules in production:

```env
WAF_ENABLED=true
WAF_MODE=block  # Options: log, block
WAF_SQL_INJECTION_PROTECTION=true
WAF_XSS_PROTECTION=true
WAF_RATE_LIMIT_REQUESTS=100
WAF_RATE_LIMIT_WINDOW=60  # seconds
```

### 2. GeoIP Blocking

Configure GeoIP database and blocking rules:

```env
GEOIP_ENABLED=true
GEOIP_DATABASE_PATH=/path/to/GeoLite2-Country.mmdb
GEOIP_BLOCKED_COUNTRIES=CN,RU,KP  # Comma-separated ISO codes
GEOIP_ALLOWED_COUNTRIES=*  # Or specify allowed countries
```

Download MaxMind GeoLite2 database:
- Visit https://dev.maxmind.com/geoip/geolite2-free-geolocation-data
- Create free account and get license key
- Download Country database
- Place in storage directory

### 3. HIBP (Have I Been Pwned) Integration

Enable password breach checking:

```env
HIBP_ENABLED=true
HIBP_API_KEY=your_hibp_api_key_here
```

Get API key from: https://haveibeenpwned.com/API/v3

### 4. Cloudflare Integration

For sites behind Cloudflare:

```env
CLOUDFLARE_ENABLED=true
CLOUDFLARE_API_TOKEN=your_cloudflare_api_token
CLOUDFLARE_ZONE_ID=your_zone_id
CLOUDFLARE_TRUST_PROXY_HEADERS=true
```

### 5. reCAPTCHA v3 for Forms

Configure Google reCAPTCHA:

```env
RECAPTCHA_ENABLED=true
RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key
RECAPTCHA_THRESHOLD=0.5
```

Get keys from: https://www.google.com/recaptcha/admin

### 6. Cloudflare Turnstile (Alternative to reCAPTCHA)

```env
TURNSTILE_ENABLED=true
TURNSTILE_SITE_KEY=your_turnstile_site_key
TURNSTILE_SECRET_KEY=your_turnstile_secret_key
```

Get keys from: https://dash.cloudflare.com/?to=/:account/turnstile

## Environment Setup Steps

1. **Copy .env.example to .env** (if not already done):
   ```bash
   cp .env.example .env
   ```

2. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

3. **Update security-related variables** in `.env` file with production values

4. **Clear configuration cache**:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

5. **Verify modules are enabled**:
   - Login to admin panel
   - Navigate to System → Security Dashboard
   - Check status of each security module

## SSL/TLS Configuration

Ensure HTTPS is enforced in production:

```env
FORCE_HTTPS=true
SESSION_SECURE_COOKIE=true
```

Add to your web server configuration:
- Redirect all HTTP traffic to HTTPS
- Enable HSTS headers
- Use TLS 1.2 or higher

## File Permissions

Set proper permissions for production:

```bash
chown -R www-data:www-data /path/to/app/storage
chmod -R 775 /path/to/app/storage
chmod -R 775 /path/to/app/bootstrap/cache
```

## Database Security

- Use strong database passwords
- Restrict database user privileges to minimum required
- Enable MySQL/MariaDB SSL connection if supported
- Regular database backups

## Monitoring

Enable logging for security events:

```env
LOG_SECURITY_EVENTS=true
SECURITY_LOG_CHANNEL=security  # Separate log channel
```

Review logs regularly:
```bash
tail -f storage/logs/security.log
```
