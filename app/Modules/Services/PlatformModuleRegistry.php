<?php

namespace App\Modules\Services;

use App\AI\Services\VisualPageGenerator;
use App\Automation\Services\AutomationEngine;
use App\Compliance\Services\ComplianceAuditService;
use App\Ecommerce\Services\OrderService;
use App\Integrations\Services\N8nService;
use App\Localization\Services\ContentTranslator;
use App\Marketplace\Services\MarketplaceCatalog;
use App\Messaging\Services\CampaignService;
use App\Recommendations\Services\RecommendationEngine;
use App\Services\Notifications\NotificationService;
use App\Services\Webhooks\WebhookService;

class PlatformModuleRegistry
{
    public function all(): array
    {
        return [
            'marketplace' => ['service' => MarketplaceCatalog::class, 'capabilities' => ['catalog', 'signature_verification']],
            'themes-marketplace' => ['service' => MarketplaceCatalog::class, 'capabilities' => ['theme_catalog', 'signature_verification']],
            'localization' => ['service' => ContentTranslator::class, 'capabilities' => ['fallback', 'localized_uri']],
            'ecommerce' => ['service' => OrderService::class, 'capabilities' => ['catalog', 'cart', 'orders', 'payments']],
            'webhooks' => ['service' => WebhookService::class, 'capabilities' => ['signed_delivery', 'retries', 'ssrf_protection']],
            'n8n' => ['service' => N8nService::class, 'capabilities' => ['signed_trigger', 'idempotency']],
            'telegram' => ['service' => NotificationService::class, 'capabilities' => ['bot_notifications', 'support_widget']],
            'campaigns' => ['service' => CampaignService::class, 'capabilities' => ['consent_filter', 'deduplication', 'queued_email']],
            'visual-ai' => ['service' => VisualPageGenerator::class, 'capabilities' => ['builder_generation', 'schema_validation']],
            'automation' => ['service' => AutomationEngine::class, 'capabilities' => ['conditions', 'bounded_execution']],
            'recommendations' => ['service' => RecommendationEngine::class, 'capabilities' => ['interest_ranking', 'seen_suppression']],
            'compliance' => ['service' => ComplianceAuditService::class, 'capabilities' => ['control_audit', 'risk_score']],
        ];
    }

    public function find(string $id): ?array
    {
        return $this->all()[$id] ?? null;
    }

    public function available(string $id): bool
    {
        $module = $this->find($id);

        return $module !== null && app()->bound($module['service']);
    }
}
