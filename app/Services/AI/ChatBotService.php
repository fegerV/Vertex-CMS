<?php

namespace App\Services\AI;

use App\Ecommerce\Models\Order;

class ChatBotService
{
    private ContentGenerationService $generationService;

    /**
     * FIX C05: Inject dependencies via constructor instead of manual instantiation
     * This allows for proper testing and adherence to dependency inversion principle
     */
    public function __construct(ContentGenerationService $generationService)
    {
        $this->generationService = $generationService;
    }

    public function handle_message(string $message, array $context = []): array
    {
        $prompt = $this->buildPrompt($message, $context);

        return $this->generationService->generateText($prompt, [
            'max_tokens' => 500,
            'temperature' => 0.7,
        ]);
    }

    private function buildPrompt(string $message, array $context = []): string
    {
        $systemPrompt = "Ты полезный ассистент интернет-магазина. Отвечай кратко и по делу.";

        $contextString = '';
        if (!empty($context['user_name'])) {
            $contextString .= "Имя пользователя: {$context['user_name']}\n";
        }

        if (!empty($context['order_status'])) {
            $contextString .= "Статус заказа: {$context['order_status']}\n";
        }

        if (!empty($context['cart_items'])) {
            $items = collect($context['cart_items'])
                ->map(fn($item) => "- {$item['name']} ({$item['price']} руб.)")
                ->join("\n");
            $contextString .= "Товары в корзине:\n{$items}\n";
        }

        return "{$systemPrompt}\n\n{$contextString}\nПользователь: {$message}\nАссистент:";
    }

    public function answerFAQ(string $question): array
    {
        // FIX C03: Get contact information from configuration instead of hardcoded values
        $faqKnowledge = [
            'доставка' => config('vertex.faq.delivery', 'Мы доставляем товары курьером по городу и СДЭК по России. Срок доставки 2-5 дней.'),
            'оплата' => config('vertex.faq.payment', 'Принимаем оплату картами онлайн, при получении и банковским переводом для юрлиц.'),
            'возврат' => config('vertex.faq.return', 'Возврат возможен в течение 14 дней с момента получения товара при сохранении товарного вида.'),
            'гарантия' => config('vertex.faq.warranty', 'Гарантия на все товары от 1 года. Сервисные центры в крупных городах.'),
            'контакты' => $this->getContactInfo(),
        ];

        // Ищем совпадения в базе знаний
        foreach ($faqKnowledge as $keyword => $answer) {
            if (stripos($question, $keyword) !== false) {
                return [
                    'success' => true,
                    'content' => $answer,
                    'source' => 'faq',
                ];
            }
        }

        // Если не нашли, используем AI
        $prompt = "Ответь на вопрос покупателя интернет-магазина:\n{$question}";

        return $this->generationService->generateText($prompt, [
            'max_tokens' => 200,
            'temperature' => 0.5,
        ]);
    }

    /**
     * Get contact information from configuration
     * FIX C03: Removed hardcoded fake phone number
     */
    private function getContactInfo(): string
    {
        $phone = config('vertex.contacts.phone');
        $email = config('vertex.contacts.email', 'support@example.com');
        $hours = config('vertex.contacts.hours', 'Чат работает 9:00-21:00 МСК');
        
        if (empty($phone) || $phone === '8-800-XXX-XX-XX') {
            // Return generic message if phone is not configured
            return "Email: {$email}, {$hours}";
        }
        
        return "Телефон: {$phone}, Email: {$email}, {$hours}";
    }

    public function recommendProducts(array $userPreferences, int $limit = 5): array
    {
        $prompt = sprintf(
            "Рекомендуй %d товаров для пользователя со следующими предпочтениями:\n" .
            "Категории интересов: %s\n" .
            "Ценовой диапазон: %s - %s руб.\n" .
            "Верни JSON массив: [{\"id\": 1, \"name\": \"...\", \"reason\": \"почему рекомендуется\"}]",
            $limit,
            implode(', ', $userPreferences['categories'] ?? ['все']),
            $userPreferences['min_price'] ?? 0,
            $userPreferences['max_price'] ?? 100000
        );

        $result = $this->generationService->generateText($prompt, [
            'max_tokens' => 500,
            'temperature' => 0.6,
        ]);

        if ($result['success']) {
            preg_match('/\[.*\]/s', $result['content'], $matches);
            if (isset($matches[0])) {
                $parsed = json_decode($matches[0], true);
                if (is_array($parsed) && json_last_error() === JSON_ERROR_NONE) {
                    return ['success' => true, 'recommendations' => $parsed];
                }
            }
        }

        return $result;
    }

    public function processOrderQuery(string $orderId, string $question): array
    {
        $order = Order::query()->find($orderId);

        if (!$order) {
            return [
                'success' => false,
                'content' => 'Заказ не найден. Проверьте номер заказа.',
            ];
        }

        $context = [
            'order_id' => $order->id,
            'status' => $order->status,
            'total' => $order->total,
            'created_at' => $order->created_at->format('d.m.Y'),
        ];

        $prompt = sprintf(
            "Информация о заказе #%s:\n" .
            "Статус: %s\n" .
            "Сумма: %s руб.\n" .
            "Дата: %s\n\n" .
            "Вопрос пользователя: %s\n" .
            "Дай точный ответ на основе этой информации.",
            $context['order_id'],
            $context['status'],
            $context['total'],
            $context['created_at'],
            $question
        );

        return $this->generationService->generateText($prompt, [
            'max_tokens' => 300,
            'temperature' => 0.3,
        ]);
    }
}
