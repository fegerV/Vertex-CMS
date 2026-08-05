<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class ContentGenerationService
{
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
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
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
