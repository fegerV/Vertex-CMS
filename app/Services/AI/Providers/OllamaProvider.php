<?php

namespace App\Services\AI\Providers;

use App\Contracts\AI\AiProviderInterface;
use Illuminate\Support\Facades\Http;

/**
 * Ollama Provider implementation (self-hosted)
 */
class OllamaProvider implements AiProviderInterface
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.base_url', 'http://localhost:11434');
    }

    public function getName(): string
    {
        return 'ollama';
    }

    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/api/tags');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function generateText(string $prompt, array $options = []): array
    {
        return $this->chat([['role' => 'user', 'content' => $prompt]], $options);
    }

    public function chat(array $messages, array $options = []): array
    {
        try {
            $response = Http::post($this->baseUrl . '/api/chat', [
                'model' => $options['model'] ?? 'llama3.2',
                'messages' => $messages,
                'stream' => false,
                'options' => [
                    'temperature' => $options['temperature'] ?? 0.7,
                    'num_predict' => $options['max_tokens'] ?? 1024,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['message']['content'] ?? '',
                    'usage' => [
                        'prompt_tokens' => $data['prompt_eval_count'] ?? 0,
                        'completion_tokens' => $data['eval_count'] ?? 0,
                    ],
                    'provider' => 'ollama',
                    'model' => $data['model'] ?? $options['model'] ?? 'llama3.2',
                ];
            }

            return [
                'success' => false,
                'error' => 'Ollama request failed',
                'provider' => 'ollama',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'ollama',
            ];
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        try {
            $response = Http::post($this->baseUrl . '/api/generate', [
                'model' => $options['model'] ?? 'stable-diffusion-xl',
                'prompt' => $prompt,
                'stream' => false,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'images' => [$data['image'] ?? null],
                    'provider' => 'ollama',
                    'model' => $options['model'] ?? 'stable-diffusion-xl',
                ];
            }

            return [
                'success' => false,
                'error' => 'Ollama image generation failed',
                'provider' => 'ollama',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => 'ollama',
            ];
        }
    }

    public function getModels(): array
    {
        try {
            $response = Http::get($this->baseUrl . '/api/tags');
            if ($response->successful()) {
                $data = $response->json();
                $models = [];
                foreach ($data['models'] ?? [] as $model) {
                    $models[$model['name']] = $model['name'];
                }
                return $models;
            }
        } catch (\Exception $e) {
            // Return default models if API call fails
        }

        return [
            'llama3.2' => 'Llama 3.2',
            'llama3.1' => 'Llama 3.1',
            'mistral' => 'Mistral',
            'codellama' => 'Code Llama',
            'stable-diffusion-xl' => 'Stable Diffusion XL (Image)',
        ];
    }
}
