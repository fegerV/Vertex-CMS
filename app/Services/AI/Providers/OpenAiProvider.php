<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;

/**
 * OpenAI Provider implementation
 */
class OpenAiProvider implements AiProviderInterface
{
    public function getName(): string
    {
        return 'openai';
    }

    public function isAvailable(): bool
    {
        return !empty(config('services.openai.api_key'));
    }

    public function generateText(string $prompt, array $options = []): array
    {
        $apiKey = config('services.openai.api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $options['model'] ?? 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? [],
                    'provider' => 'openai',
                    'model' => $data['model'] ?? $options['model'] ?? 'gpt-3.5-turbo',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
                'provider' => 'openai',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'openai',
            ];
        }
    }

    public function chat(array $messages, array $options = []): array
    {
        $apiKey = config('services.openai.api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $options['model'] ?? 'gpt-3.5-turbo',
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? [],
                    'provider' => 'openai',
                    'model' => $data['model'] ?? $options['model'] ?? 'gpt-3.5-turbo',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
                'provider' => 'openai',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'openai',
            ];
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        $apiKey = config('services.openai.api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/images/generations', [
                'model' => $options['model'] ?? 'dall-e-3',
                'prompt' => $prompt,
                'n' => $options['n'] ?? 1,
                'size' => $options['size'] ?? '1024x1024',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $images = collect($data['data'] ?? [])
                    ->map(fn($img) => $img['url'] ?? $img['b64_json'] ?? null)
                    ->filter()
                    ->values()
                    ->toArray();

                return [
                    'success' => true,
                    'images' => $images,
                    'provider' => 'openai',
                    'model' => $options['model'] ?? 'dall-e-3',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
                'provider' => 'openai',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'openai',
            ];
        }
    }

    public function getModels(): array
    {
        return [
            'gpt-4o' => 'GPT-4o (Latest)',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'dall-e-3' => 'DALL-E 3 (Image)',
            'dall-e-2' => 'DALL-E 2 (Image)',
        ];
    }
}
