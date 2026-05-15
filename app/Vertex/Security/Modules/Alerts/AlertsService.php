<?php

namespace App\Vertex\Security\Modules\Alerts;

use App\System\Services\SystemInfoService;
use App\Vertex\Security\Modules\Integrity\IntegrityService;
use App\Vertex\Security\Modules\Scanner\ScannerService;

class AlertsService
{
    public function __construct(
        private readonly SystemInfoService $systemInfo,
        private readonly IntegrityService $integrity,
        private readonly ScannerService $scanner,
    ) {
    }

    /**
     * @return array{
     *     enabled: bool,
     *     status: string,
     *     summary: string,
     *     counts: array<string, int>,
     *     alerts: array<int, array<string, string>>
     * }
     */
    public function getStatus(): array
    {
        if (! config('security.modules.alerts', false)) {
            return [
                'enabled' => false,
                'status' => 'disabled',
                'summary' => 'Модуль реактивных предупреждений отключен в конфигурации.',
                'counts' => [
                    'total' => 0,
                    'danger' => 0,
                    'warning' => 0,
                ],
                'alerts' => [],
            ];
        }

        $alerts = $this->activeAlerts();
        $dangerCount = collect($alerts)->where('severity', 'danger')->count();
        $warningCount = collect($alerts)->where('severity', 'warning')->count();

        return [
            'enabled' => true,
            'status' => empty($alerts) ? 'monitoring' : 'issues_detected',
            'summary' => empty($alerts)
                ? 'Критичных конфигурационных рисков не обнаружено.'
                : 'Найдены предупреждения, требующие внимания администратора.',
            'counts' => [
                'total' => count($alerts),
                'danger' => $dangerCount,
                'warning' => $warningCount,
            ],
            'alerts' => $alerts,
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function activeAlerts(): array
    {
        $alerts = [];
        $info = $this->systemInfo->get();
        $integrity = $this->integrity->getStatus();
        $scanner = $this->scanner->getStatus();

        if ((bool) config('app.debug', false)) {
            $alerts[] = $this->makeAlert(
                'app-debug-enabled',
                'warning',
                'APP_DEBUG включен',
                'Приложение работает с активным debug-режимом. Для production это повышает риск утечки внутренних данных.',
                'core'
            );
        }

        if (! (bool) config('security.audit.enabled', true)) {
            $alerts[] = $this->makeAlert(
                'audit-disabled',
                'warning',
                'Security audit отключен',
                'Аудит security-событий выключен. Критичные изменения и действия администраторов будут хуже отслеживаться.',
                'alerts'
            );
        }

        if (! (bool) config('security.session.secure', true)) {
            $alerts[] = $this->makeAlert(
                'session-insecure-cookie',
                'warning',
                'Session cookies не помечены как secure',
                'Cookie сессии могут передаваться без HTTPS-защиты. Для админки это нежелательная конфигурация.',
                'core'
            );
        }

        if (! (bool) config('security.headers.hsts.enabled', true)) {
            $alerts[] = $this->makeAlert(
                'hsts-disabled',
                'warning',
                'HSTS выключен',
                'Strict-Transport-Security отключен. Браузеры не будут принудительно фиксировать HTTPS для сайта.',
                'core'
            );
        }

        if ((int) config('security.password_policy.min_length', 12) < 12) {
            $alerts[] = $this->makeAlert(
                'password-policy-weak',
                'warning',
                'Ослабленная password policy',
                'Минимальная длина пароля ниже рекомендованного уровня 12 символов.',
                'core'
            );
        }

        if (! ($info['storage_writable'] ?? false)) {
            $alerts[] = $this->makeAlert(
                'storage-not-writable',
                'danger',
                'Storage недоступен для записи',
                'Директория storage недоступна для записи. Это может ломать логи, кэш, загрузки и baseline security-модулей.',
                'system'
            );
        }

        if (! ($info['cache_writable'] ?? false)) {
            $alerts[] = $this->makeAlert(
                'cache-not-writable',
                'danger',
                'Cache директория недоступна для записи',
                'Система не может надежно писать кэш. Это влияет на производительность и на fallback-механизмы.',
                'system'
            );
        }

        if (! ($info['uploads_writable'] ?? false)) {
            $alerts[] = $this->makeAlert(
                'uploads-not-writable',
                'danger',
                'Uploads директория недоступна для записи',
                'Загрузка файлов может завершаться ошибками, а media/runtime-потоки будут работать нестабильно.',
                'system'
            );
        }

        if (! (bool) config('security.modules.integrity', false)) {
            $alerts[] = $this->makeAlert(
                'integrity-disabled',
                'warning',
                'Integrity Monitor выключен',
                'Контроль целостности файлов отключен. Изменения в коде и шаблонах останутся без baseline-проверки.',
                'integrity'
            );
        } elseif (($integrity['status'] ?? null) === 'not_initialized') {
            $alerts[] = $this->makeAlert(
                'integrity-not-initialized',
                'warning',
                'Integrity baseline не инициализирован',
                'Модуль уже активен, но эталонное состояние файлов еще не сохранено. Запустите инициализацию baseline.',
                'integrity'
            );
        } elseif (($integrity['status'] ?? null) === 'drift_detected') {
            $alerts[] = $this->makeAlert(
                'integrity-drift-detected',
                'danger',
                'Integrity обнаружил дрейф файлов',
                'Текущее состояние файлов отличается от baseline. Проверьте список измененных, добавленных и удаленных файлов.',
                'integrity'
            );
        }

        if (($scanner['enabled'] ?? false) && ($scanner['status'] ?? null) === 'not_scanned') {
            $alerts[] = $this->makeAlert(
                'scanner-not-scanned',
                'warning',
                'Security Scanner еще не запускался',
                'Фоновый scanner включен, но отчет еще не создан. Запустите security:scanner:run или дождитесь планового прогона.',
                'scanner'
            );
        }

        if (($scanner['enabled'] ?? false) && (bool) ($scanner['is_stale'] ?? false)) {
            $alerts[] = $this->makeAlert(
                'scanner-stale-report',
                'warning',
                'Отчет Scanner устарел',
                'Последний отчет scanner слишком старый. Для актуальной картины угроз нужен новый фоновый прогон.',
                'scanner'
            );
        }

        if (($scanner['enabled'] ?? false) && ($scanner['status'] ?? null) === 'issues_detected') {
            $alerts[] = $this->makeAlert(
                'scanner-issues-detected',
                'danger',
                'Scanner нашел подозрительные файлы',
                'Последний security scan обнаружил аномалии в uploads или медиатеке. Откройте dashboard и проверьте findings.',
                'scanner'
            );
        }

        return $alerts;
    }

    /**
     * @return array<string, string>
     */
    private function makeAlert(string $id, string $severity, string $title, string $message, string $source): array
    {
        return [
            'id' => $id,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'source' => $source,
        ];
    }
}
