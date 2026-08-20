<?php

namespace App\Services\AI;

use App\Contracts\AI\AiProviderInterface;
use App\Services\AI\Providers\OpenAiProvider;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\OllamaProvider;
use Illuminate\Support\Facades\Http;

class ContentGenerationService
{
    protected ?AiProviderInterface $provider = null;

    public function __construct()
    {
        $this->provider = $this->resolveProvider();
    }

    /**
     * Resolve the AI provider based on configuration
     */
    protected function resolveProvider(): ?AiProviderInterface
    {
        $defaultProvider = config('ai.default_provider', 'openai');

        return match ($defaultProvider) {
            'anthropic' => new AnthropicProvider(),
            'ollama' => new OllamaProvider(),
            'openai', default => new OpenAiProvider(),
        };
    }

    /**
     * Set a specific provider
     */
    public function setProvider(AiProviderInterface $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Get available providers
     */
    public function getAvailableProviders(): array
    {
        $providers = [
            new OpenAiProvider(),
            new AnthropicProvider(),
            new OllamaProvider(),
        ];

        return array_filter($providers, fn($p) => $p->isAvailable());
    }

    public function generateText(string $prompt, array $options = []): array
    {
        if (!$this->provider) {
            return ['success' => false, 'error' => 'No AI provider configured'];
        }

        if (!$this->provider->isAvailable()) {
            return ['success' => false, 'error' => 'AI provider is not available'];
        }

        return $this->provider->generateText($prompt, $options);
    }

    public function chat(array $messages, array $options = []): array
    {
        if (!$this->provider) {
            return ['success' => false, 'error' => 'No AI provider configured'];
        }

        if (!$this->provider->isAvailable()) {
            return ['success' => false, 'error' => 'AI provider is not available'];
        }

        return $this->provider->chat($messages, $options);
    }

    public function generateProductDescription(array $productData): array
    {
        $prompt = sprintf(
            "Создай привлекательное описание товара для интернет-магазина.\n\n" .
            "Название: %s\n" .
            "Категория: %s\n" .
            "Цена: %s\n" .
            "Характеристики:\n%s\n\n" .
            "Описание должно быть информативным, убедительным и оптимизированным для SEO.",
            $productData['name'],
            $productData['category'] ?? 'Не указана',
            $productData['price'] ?? 'Не указана',
            collect($productData['features'] ?? [])
                ->map(fn($feature) => "- {$feature}")
                ->join("\n")
        );

        return $this->generateText($prompt, [
            'max_tokens' => 500,
            'temperature' => 0.8,
        ]);
    }

    public function generateMetaTags(array $pageData): array
    {
        $prompt = sprintf(
            "Создай SEO-оптимизированные meta title и meta description для страницы.\n\n" .
            "Заголовок страницы: %s\n" .
            "Контент: %s\n" .
            "Ключевые слова: %s\n\n" .
            "Верни ответ в формате JSON: {\"title\": \"...\", \"description\": \"...\"}",
            $pageData['title'] ?? '',
            substr($pageData['content'] ?? '', 0, 500),
            implode(', ', $pageData['keywords'] ?? [])
        );

        $result = $this->generateText($prompt, [
            'max_tokens' => 200,
            'temperature' => 0.5,
        ]);

        if ($result['success']) {
            // Парсим JSON из ответа
            preg_match('/\{.*\}/s', $result['content'], $matches);
            if (isset($matches[0])) {
                $parsed = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return ['success' => true] + $parsed;
                }
            }
        }

        return $result;
    }

    public function generateBlogPost(array $topicData): array
    {
        $prompt = sprintf(
            "Напиши подробную статью для блога на тему: %s\n\n" .
            "Целевая аудитория: %s\n" .
            "Ключевые моменты для раскрытия:\n%s\n\n" .
            "Статья должна быть структурированной, с заголовками H2-H3, списками и выводами.",
            $topicData['topic'],
            $topicData['audience'] ?? 'Широкая аудитория',
            collect($topicData['points'] ?? [])
                ->map(fn($point) => "- {$point}")
                ->join("\n")
        );

        return $this->generateText($prompt, [
            'max_tokens' => 2000,
            'temperature' => 0.7,
        ]);
    }

    public function summarizeText(string $text, int $maxLength = 200): array
    {
        $prompt = sprintf(
            "Сделай краткое содержание следующего текста (максимум %d символов):\n\n%s",
            $maxLength,
            substr($text, 0, 3000)
        );

        return $this->generateText($prompt, [
            'max_tokens' => 300,
            'temperature' => 0.3,
        ]);
    }
}
