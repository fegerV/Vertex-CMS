<?php

namespace App\Vertex\Security\Services;

use App\Vertex\Security\Modules\Alerts\AlertsService;
use App\Vertex\Security\Modules\Integrity\IntegrityService;
use App\Vertex\Security\Modules\Scanner\ScannerService;
use App\Vertex\Security\Support\ModuleRegistry;
use Illuminate\Support\Collection;

class SecurityDashboardService
{
    public function __construct(
        private readonly ModuleRegistry $modules,
        private readonly IntegrityService $integrity,
        private readonly ScannerService $scanner,
        private readonly AlertsService $alerts,
    ) {
    }

    /**
     * @return array{
     *     core: array<string, mixed>,
     *     totals: array<string, int>,
     *     modules: array<int, array<string, mixed>>,
     *     integrity: array<string, mixed>,
     *     scanner: array<string, mixed>,
     *     alerts: array<string, mixed>
     * }
     */
    public function overview(): array
    {
        $integrity = $this->integrity->getStatus();
        $scanner = $this->scanner->getStatus();
        $alerts = $this->alerts->getStatus();
        $modules = $this->buildModules($integrity, $scanner, $alerts);

        return [
            'core' => [
                'enabled' => $this->modules->coreEnabled(),
                'middleware' => [
                    'secure_headers' => \App\Vertex\Security\Middleware\SecureHeaders::class,
                    'session_guard' => \App\Vertex\Security\Middleware\SessionGuard::class,
                    'basic_rate_limiter' => \App\Vertex\Security\Middleware\BasicRateLimiter::class,
                ],
                'cache_driver' => (string) config('cache.default'),
                'queue_driver' => (string) config('queue.default'),
                'password_min_length' => (int) config('security.password_policy.min_length', 12),
                'session_rotation_minutes' => (int) config('security.session.rotation_minutes', 30),
                'audit_enabled' => (bool) config('security.audit.enabled', true),
            ],
            'totals' => [
                'enabled_modules' => $modules->where('enabled', true)->count(),
                'implemented_modules' => $modules->where('implemented', true)->count(),
                'warning_modules' => $modules->filter(fn (array $module): bool => in_array($module['severity'], ['warning', 'danger'], true))->count(),
                'active_alerts' => (int) ($alerts['counts']['total'] ?? 0),
            ],
            'modules' => $modules->values()->all(),
            'integrity' => $integrity,
            'scanner' => $scanner,
            'alerts' => $alerts,
        ];
    }

    /**
     * @param  array<string, mixed>  $integrity
     * @param  array<string, mixed>  $scanner
     * @param  array<string, mixed>  $alerts
     * @return Collection<int, array<string, mixed>>
     */
    private function buildModules(array $integrity, array $scanner, array $alerts): Collection
    {
        $definitions = [
            'waf' => [
                'name' => 'WAF Rules Engine',
                'description' => 'HTTP-правила и защита от подозрительных запросов.',
                'implemented' => false,
            ],
            'geoip' => [
                'name' => 'GeoIP / IP Blocker',
                'description' => 'Фильтрация по IP, странам и сетям.',
                'implemented' => false,
            ],
            'integrity' => [
                'name' => 'Integrity Monitor',
                'description' => 'Контроль целостности файловой структуры и baseline-сканирование.',
                'implemented' => true,
            ],
            'hibp' => [
                'name' => 'HIBP Check',
                'description' => 'Проверка паролей по k-anonymity без утечки полного хэша.',
                'implemented' => false,
            ],
            'cloudflare' => [
                'name' => 'Cloudflare Sync',
                'description' => 'Синхронизация правил и внешнего edge-периметра.',
                'implemented' => false,
            ],
            'scanner' => [
                'name' => 'Scanner',
                'description' => 'Расширенное фоновое сканирование и поиск аномалий.',
                'implemented' => true,
            ],
            'alerts' => [
                'name' => 'Real-time Alerts',
                'description' => 'Оповещения о критичных событиях и рисках конфигурации.',
                'implemented' => true,
            ],
        ];

        return collect($definitions)->map(function (array $definition, string $key) use ($integrity, $scanner, $alerts): array {
            if ($key === 'integrity') {
                return $this->buildIntegrityModule($definition, $integrity);
            }

            if ($key === 'scanner') {
                return $this->buildScannerModule($definition, $scanner);
            }

            if ($key === 'alerts') {
                return $this->buildAlertsModule($definition, $alerts);
            }

            return $this->buildStubModule($key, $definition);
        });
    }

    /**
     * @param  array{name:string,description:string,implemented:bool}  $definition
     * @param  array<string, mixed>  $integrity
     * @return array<string, mixed>
     */
    private function buildIntegrityModule(array $definition, array $integrity): array
    {
        $status = (string) ($integrity['status'] ?? 'disabled');

        return [
            'key' => 'integrity',
            'name' => $definition['name'],
            'description' => $definition['description'],
            'enabled' => (bool) ($integrity['enabled'] ?? false),
            'implemented' => true,
            'status' => $status,
            'status_label' => $this->labelForStatus($status),
            'severity' => $this->severityForStatus($status),
            'summary' => (string) ($integrity['summary'] ?? 'Module status is unavailable.'),
            'details' => [
                'tracked_files' => (int) ($integrity['tracked_files'] ?? 0),
                'changed_count' => (int) ($integrity['changed_count'] ?? 0),
                'added_count' => (int) ($integrity['added_count'] ?? 0),
                'removed_count' => (int) ($integrity['removed_count'] ?? 0),
                'last_scanned_at' => $integrity['last_scanned_at'] ?? null,
                'baseline_created_at' => $integrity['baseline_created_at'] ?? null,
            ],
        ];
    }

    /**
     * @param  array{name:string,description:string,implemented:bool}  $definition
     * @param  array<string, mixed>  $scanner
     * @return array<string, mixed>
     */
    private function buildScannerModule(array $definition, array $scanner): array
    {
        $status = (string) ($scanner['status'] ?? 'disabled');
        $isStale = (bool) ($scanner['is_stale'] ?? false);
        $dangerCount = (int) ($scanner['counts']['danger'] ?? 0);
        $warningCount = (int) ($scanner['counts']['warning'] ?? 0);

        $severity = match (true) {
            $dangerCount > 0 => 'danger',
            $warningCount > 0 || $status === 'not_scanned' || $isStale => 'warning',
            $status === 'clean' => 'success',
            default => 'muted',
        };

        $label = match (true) {
            $status === 'disabled' => 'Выключен',
            $status === 'not_scanned' => 'Еще не запускался',
            $isStale => 'Отчет устарел',
            $status === 'issues_detected' => 'Есть находки',
            $status === 'clean' => 'Чисто',
            default => 'Неизвестно',
        };

        return [
            'key' => 'scanner',
            'name' => $definition['name'],
            'description' => $definition['description'],
            'enabled' => (bool) ($scanner['enabled'] ?? false),
            'implemented' => true,
            'status' => $status,
            'status_label' => $label,
            'severity' => $severity,
            'summary' => (string) ($scanner['summary'] ?? 'Scanner status is unavailable.'),
            'details' => [
                'scanned_files' => (int) ($scanner['scanned_files'] ?? 0),
                'total' => (int) ($scanner['counts']['total'] ?? 0),
                'danger' => $dangerCount,
                'warning' => $warningCount,
                'last_scanned_at' => $scanner['last_scanned_at'] ?? null,
                'is_stale' => $isStale,
            ],
        ];
    }

    /**
     * @param  array{name:string,description:string,implemented:bool}  $definition
     * @param  array<string, mixed>  $alerts
     * @return array<string, mixed>
     */
    private function buildAlertsModule(array $definition, array $alerts): array
    {
        $status = (string) ($alerts['status'] ?? 'disabled');
        $dangerCount = (int) ($alerts['counts']['danger'] ?? 0);
        $warningCount = (int) ($alerts['counts']['warning'] ?? 0);
        $severity = match (true) {
            $dangerCount > 0 => 'danger',
            $warningCount > 0 => 'warning',
            $status === 'monitoring' => 'success',
            default => 'muted',
        };

        return [
            'key' => 'alerts',
            'name' => $definition['name'],
            'description' => $definition['description'],
            'enabled' => (bool) ($alerts['enabled'] ?? false),
            'implemented' => true,
            'status' => $status,
            'status_label' => match ($status) {
                'monitoring' => 'Мониторинг',
                'issues_detected' => 'Есть предупреждения',
                'disabled' => 'Выключен',
                default => 'Неизвестно',
            },
            'severity' => $severity,
            'summary' => (string) ($alerts['summary'] ?? 'Статус предупреждений недоступен.'),
            'details' => [
                'total' => (int) ($alerts['counts']['total'] ?? 0),
                'danger' => $dangerCount,
                'warning' => $warningCount,
            ],
        ];
    }

    /**
     * @param  array{name:string,description:string,implemented:bool}  $definition
     * @return array<string, mixed>
     */
    private function buildStubModule(string $key, array $definition): array
    {
        $enabled = $this->modules->isEnabled($key);
        $status = $enabled ? 'scaffold' : 'disabled';

        return [
            'key' => $key,
            'name' => $definition['name'],
            'description' => $definition['description'],
            'enabled' => $enabled,
            'implemented' => $definition['implemented'],
            'status' => $status,
            'status_label' => $enabled ? 'Каркас' : 'Выключен',
            'severity' => $enabled ? 'warning' : 'muted',
            'summary' => $enabled
                ? 'Тумблер активен, но модуль пока оставлен каркасом без отдельного runtime-пайплайна.'
                : 'Модуль отключен конфигом и не влияет на runtime.',
            'details' => [],
        ];
    }

    private function labelForStatus(string $status): string
    {
        return match ($status) {
            'clean' => 'Чисто',
            'baseline_ready' => 'Готов к скану',
            'not_initialized' => 'Нужна инициализация',
            'drift_detected' => 'Найдены изменения',
            'disabled' => 'Выключен',
            default => 'Неизвестно',
        };
    }

    private function severityForStatus(string $status): string
    {
        return match ($status) {
            'clean', 'baseline_ready' => 'success',
            'not_initialized' => 'warning',
            'drift_detected' => 'danger',
            'disabled' => 'muted',
            default => 'muted',
        };
    }
}
