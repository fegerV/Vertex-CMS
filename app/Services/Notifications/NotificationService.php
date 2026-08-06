<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Http;

class NotificationService
{
    public function sendToTelegram(string $chatId, string $message, array $options = []): array
    {
        $token = config('services.telegram.bot_token');
        
        if (!$token) {
            return ['success' => false, 'error' => 'Telegram bot token not configured'];
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        try {
            $response = Http::post($url, [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => $options['parse_mode'] ?? 'HTML',
                'disable_web_page_preview' => $options['disable_web_page_preview'] ?? false,
            ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendToSlack(string $webhookUrl, string $message, array $blocks = []): array
    {
        try {
            $payload = [
                'text' => $message,
                'blocks' => !empty($blocks) ? $blocks : [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $message,
                        ],
                    ],
                ],
            ];

            $response = Http::post($webhookUrl, $payload);

            return [
                'success' => $response->successful(),
                'data' => $response->body(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sendOrderNotification(array $order, string $channel = 'telegram'): array
    {
        $message = sprintf(
            "🛒 <b>Новый заказ #%s</b>\n\n" .
            "Клиент: %s\n" .
            "Сумма: %s %s\n" .
            "Статус: %s\n\n" .
            "Товары:\n%s",
            $order['id'],
            $order['customer_name'] ?? 'Не указан',
            $order['total'],
            $order['currency'] ?? 'RUB',
            $order['status'],
            collect($order['items'] ?? [])
                ->map(fn($item) => "- {$item['name']} x{$item['quantity']}")
                ->join("\n")
        );

        if ($channel === 'telegram') {
            $chatId = config('services.telegram.chat_id');
            return $this->sendToTelegram($chatId, $message);
        } elseif ($channel === 'slack') {
            $webhookUrl = config('services.slack.webhook_url');
            return $this->sendToSlack($webhookUrl, strip_tags($message));
        }

        return ['success' => false, 'error' => 'Unknown channel'];
    }

    public function sendErrorNotification(\Throwable $exception, string $context = ''): array
    {
        $message = sprintf(
            "⚠️ <b>Ошибка в приложении</b>\n\n" .
            "Контекст: %s\n" .
            "Ошибка: %s\n" .
            "Файл: %s:%d\n" .
            "Время: %s",
            $context,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            now()->format('Y-m-d H:i:s')
        );

        $chatId = config('services.telegram.chat_id');
        return $this->sendToTelegram($chatId, $message);
    }
}
