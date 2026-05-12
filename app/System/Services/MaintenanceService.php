<?php

namespace App\System\Services;

use App\Core\Services\SettingsService;
use Illuminate\Support\Arr;

class MaintenanceService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function isEnabled(): bool
    {
        return (bool) $this->settings->get('maintenance.enabled', false);
    }

    public function getTheme(): string
    {
        return $this->settings->get('maintenance.theme', 'minimal');
    }

    public function getBackgroundImage(): ?int
    {
        $mediaId = $this->settings->get('maintenance.background_image');
        return $mediaId ? (int) $mediaId : null;
    }

    public function hasBackgroundBlur(): bool
    {
        return (bool) $this->settings->get('maintenance.background_blur', false);
    }

    public function getLogo(): ?int
    {
        $mediaId = $this->settings->get('maintenance.logo');
        return $mediaId ? (int) $mediaId : null;
    }

    public function getColors(): array
    {
        return [
            'primary' => $this->settings->get('maintenance.primary_color', '#3b82f6'),
            'secondary' => $this->settings->get('maintenance.secondary_color', '#6b7280'),
        ];
    }

    public function getTitle(): string
    {
        return $this->settings->get('maintenance.title', 'Сайт на обслуживании');
    }

    public function getSlogan(): string
    {
        return $this->settings->get('maintenance.slogan', 'Мы обновляем сайт. Скоро вернемся!');
    }

    public function getText(): string
    {
        return $this->settings->get('maintenance.text', '');
    }

    public function isLoginFormEnabled(): bool
    {
        return (bool) $this->settings->get('maintenance.show_login_form', true);
    }

    public function getGoogleAnalyticsId(): ?string
    {
        $id = $this->settings->get('maintenance.google_analytics_id');
        return $id ?: null;
    }

    public function getExcludedPages(): array
    {
        $pages = $this->settings->get('maintenance.excluded_pages', []);
        return is_array($pages) ? $pages : [];
    }

    public function isExcluded(string $uri): bool
    {
        $excluded = $this->getExcludedPages();
        if (empty($excluded)) {
            return false;
        }

        foreach ($excluded as $pattern) {
            if (fnmatch($pattern, $uri)) {
                return true;
            }
        }

        return false;
    }

    public function isCacheCompatibilityEnabled(): bool
    {
        return (bool) $this->settings->get('maintenance.cache_compatibility', false);
    }

    public function getHttpStatusCode(): int
    {
        return (int) $this->settings->get('maintenance.http_status_code', 503);
    }

    public function shouldShowForIp(string $ip): bool
    {
        $allowedIps = $this->getAllowedIps();
        return empty($allowedIps) || in_array($ip, $allowedIps, true);
    }

    public function getAllowedIps(): array
    {
        $ips = $this->settings->get('maintenance.allowed_ips', '');
        if (!$ips) {
            return [];
        }
        return array_filter(array_map('trim', explode("\n", $ips)));
    }

    public function bypassForAdmins(): bool
    {
        return (bool) $this->settings->get('maintenance.bypass_for_admins', true);
    }
}
