<?php

namespace App\Compliance\Services;

class ComplianceAuditService
{
    public function audit(array $configuration): array
    {
        $findings = [];
        $this->require($findings, $configuration, 'privacy_policy_url', 'critical', 'A public privacy policy is required.');
        $this->require($findings, $configuration, 'data_export_enabled', 'critical', 'Data subject export must be enabled.');
        $this->require($findings, $configuration, 'data_deletion_enabled', 'critical', 'Data deletion workflow must be enabled.');
        $this->require($findings, $configuration, 'consent_logging', 'warning', 'Consent evidence should be retained.');
        if ((int) ($configuration['retention_days'] ?? 0) <= 0) {
            $findings[] = $this->finding('retention_days', 'warning', 'A finite retention period is required.');
        }

        return ['compliant' => ! collect($findings)->contains('severity', 'critical'), 'score' => max(0, 100 - collect($findings)->sum(fn ($f) => $f['severity'] === 'critical' ? 25 : 10)), 'findings' => $findings, 'audited_at' => now()->toIso8601String()];
    }

    private function require(array &$findings, array $configuration, string $key, string $severity, string $message): void
    {
        if (blank($configuration[$key] ?? null)) {
            $findings[] = $this->finding($key, $severity, $message);
        }
    }

    private function finding(string $control, string $severity, string $message): array
    {
        return compact('control', 'severity', 'message');
    }
}
