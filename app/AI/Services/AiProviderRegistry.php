<?php

namespace App\AI\Services;

use App\Core\Services\SettingsService;

class AiProviderRegistry
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function all(): array
    {
        $defaultProvider = (string) $this->settings->get('ai.default_provider', 'openai');
        $defaultModel = (string) $this->settings->get('ai.default_model', '');

        return [
            [
                'id' => 'openai',
                'name' => 'OpenAI',
                'configured' => filled($this->settings->get('ai.openai_api_key')),
                'active' => $defaultProvider === 'openai',
                'default_model' => $defaultModel,
                'supports' => ['text', 'faq', 'cta', 'seo', 'builder'],
            ],
            [
                'id' => 'anthropic',
                'name' => 'Anthropic',
                'configured' => filled($this->settings->get('ai.anthropic_api_key')),
                'active' => $defaultProvider === 'anthropic',
                'default_model' => $defaultModel,
                'supports' => ['text', 'faq', 'cta', 'seo', 'builder'],
            ],
            [
                'id' => 'custom',
                'name' => 'Custom',
                'configured' => filled($this->settings->get('ai.custom_api_base'))
                    && filled($this->settings->get('ai.custom_api_key')),
                'active' => $defaultProvider === 'custom',
                'default_model' => $defaultModel,
                'supports' => ['text', 'faq', 'cta', 'seo', 'builder'],
            ],
        ];
    }

    public function find(?string $providerId = null): ?array
    {
        $providerId = $providerId ?: (string) $this->settings->get('ai.default_provider', 'openai');

        return collect($this->all())
            ->firstWhere('id', $providerId);
    }
}
