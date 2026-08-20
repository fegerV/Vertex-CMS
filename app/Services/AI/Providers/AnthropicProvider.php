<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;

/**
 * Anthropic Provider implementation
 */
class AnthropicProvider implements AiProviderInterface
{
    public function getName(): string
    {
        return 'anthropic';
    }

    public function isAvailable(): bool
    {
        return !empty(config('services.anthropic.api_key'));
    }

    public function generateText(string $prompt, array $options = []): array
    {
        return $this->chat([['role' => 'user', 'content' => $prompt]], $options);
    }

    public function chat(array $messages, array $options = []): array
    {
        $apiKey = config('services.anthropic.api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'Anthropic API key not configured'];
        }

        try {
            // Convert messages to Anthropic format
            $anthropicMessages = [];
            foreach ($messages as $msg) {
                $anthropicMessages[] = [
                    'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                    'content' => $msg['content'],
                ];
            }

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => $options['model'] ?? 'claude-3-haiku-20240307',
                'messages' => $anthropicMessages,
                'max_tokens' => $options['max_tokens'] ?? 1024,
                'temperature' => $options['temperature'] ?? 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['content'][0]['text'] ?? '',
                    'usage' => [
                        'input_tokens' => $data['usage']['input_tokens'] ?? 0,
                        'output_tokens' => $data['usage']['output_tokens'] ?? 0,
                    ],
                    'provider' => 'anthropic',
                    'model' => $data['model'] ?? $options['model'] ?? 'claude-3-haiku-20240307',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
                'provider' => 'anthropic',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'anthropic',
            ];
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        // Anthropic doesn't support image generation directly
        return [
            'success' => false,
            'error' => 'Anthropic does not support image generation',
            'provider' => 'anthropic',
        ];
    }

    public function getModels(): array
    {
        return [
            'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
            'claude-3-opus-20240229' => 'Claude 3 Opus',
            'claude-3-sonnet-20240229' => 'Claude 3 Sonnet',
            'claude-3-haiku-20240307' => 'Claude 3 Haiku',
            'claude-2.1' => 'Claude 2.1',
        ];
    }
}
