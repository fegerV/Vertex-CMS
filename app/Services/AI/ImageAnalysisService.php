<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageAnalysisService
{
    public function generateAITags(string $imagePath): array
    {
        $apiKey = config('services.openai.api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            // Получаем изображение
            $imageData = Storage::disk('public')->get($imagePath);
            $base64Image = base64_encode($imageData);
            $mimeType = Storage::disk('public')->mimeType($imagePath);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4-vision-preview',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Проанализируй это изображение и верни JSON с тегами: {"tags": ["tag1", "tag2"], "description": "краткое описание", "colors": ["color1", "color2"]}',
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$base64Image}",
                                ],
                            ],
                        ],
                    ],
                ],
                'max_tokens' => 300,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '';
                
                // Парсим JSON из ответа
                preg_match('/\{.*\}/s', $content, $matches);
                if (isset($matches[0])) {
                    $parsed = json_decode($matches[0], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return ['success' => true] + $parsed;
                    }
                }

                return [
                    'success' => true,
                    'tags' => ['image', 'analyzed'],
                    'description' => $content,
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

    public function moderateContent(string $text): array
    {
        $apiKey = config('services.openai.api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/moderations', [
                'input' => $text,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = $data['results'][0] ?? [];

                return [
                    'success' => true,
                    'flagged' => $result['flagged'] ?? false,
                    'categories' => $result['categories'] ?? [],
                    'category_scores' => $result['category_scores'] ?? [],
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

    public function extractKeywords(string $text, int $limit = 10): array
    {
        $prompt = sprintf(
            "Извлеки %d ключевых слов/фраз из следующего текста. Верни только JSON массив строк:\n\n%s",
            $limit,
            substr($text, 0, 2000)
        );

        $generationService = new ContentGenerationService();
        $result = $generationService->generateText($prompt, [
            'max_tokens' => 200,
            'temperature' => 0.3,
        ]);

        if ($result['success']) {
            // Парсим JSON массив из ответа
            preg_match('/\[.*\]/s', $result['content'], $matches);
            if (isset($matches[0])) {
                $parsed = json_decode($matches[0], true);
                if (is_array($parsed) && json_last_error() === JSON_ERROR_NONE) {
                    return ['success' => true, 'keywords' => $parsed];
                }
            }
        }

        return $result;
    }

    public function detectLanguage(string $text): array
    {
        $prompt = sprintf(
            "Определи язык следующего текста. Верни только код языка в формате ISO 639-1 (например, 'ru', 'en'):\n\n%s",
            substr($text, 0, 500)
        );

        $generationService = new ContentGenerationService();
        $result = $generationService->generateText($prompt, [
            'max_tokens' => 10,
            'temperature' => 0.1,
        ]);

        if ($result['success']) {
            $language = trim(strtolower($result['content']));
            // Извлекаем только код языка
            preg_match('/\b([a-z]{2})\b/', $language, $matches);
            
            return [
                'success' => true,
                'language' => $matches[1] ?? 'unknown',
                'detected_text' => $language,
            ];
        }

        return $result;
    }
}
