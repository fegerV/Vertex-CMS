<?php

/**
 * Локальная заглушка OpenAI-совместимого провайдера для smoke-проверки
 * AI-агента конструктора (`php artisan builder:ai-smoke`).
 *
 * Запускается встроенным сервером PHP: php -S 127.0.0.1:<port> scripts/smoke/ai-provider-stub.php
 */

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/health') {
    header('Content-Type: text/plain');
    echo 'ok';

    return;
}

if ($method !== 'POST' || ! str_contains($path, 'chat/completions')) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => ['message' => 'Not found']]);

    return;
}

$body = file_get_contents('php://input') ?: '';

if ($log = getenv('VC_AI_STUB_LOG')) {
    file_put_contents($log, $body."\n---\n", FILE_APPEND);
}

$payload = json_decode($body, true) ?: [];
$messages = $payload['messages'] ?? [];
$system = (string) ($messages[0]['content'] ?? '');

$scope = 'page';
if (str_contains($system, 'верни его в поле block')) {
    $scope = 'block';
} elseif (str_contains($system, 'верни её целиком в поле section')) {
    $scope = 'section';
}

$answer = match ($scope) {
    'block' => [
        'message' => 'Переписал заголовок короче',
        'block' => [
            'type' => 'heading',
            'settings' => ['text' => 'Зерно — кофе каждый день', 'level' => 'h1'],
        ],
    ],
    'section' => [
        'message' => 'Пересобрал первую секцию',
        'section' => [
            'settings' => ['padding_top' => 96, 'padding_bottom' => 96],
            'blocks' => [
                ['type' => 'heading', 'settings' => ['text' => 'Секция пересобрана', 'level' => 'h2']],
                ['type' => 'button', 'settings' => ['text' => 'Записаться', 'url' => '/booking', 'style' => 'primary']],
            ],
        ],
    ],
    default => [
        'message' => 'Собрал лендинг кофейни',
        'sections' => [
            [
                'id' => 'smoke-hero',
                'settings' => ['padding_top' => 80, 'padding_bottom' => 80, 'background_color' => '#faf7f2'],
                'blocks' => [
                    ['type' => 'heading', 'settings' => ['text' => 'Кофейня «Зерно»', 'level' => 'h1']],
                    ['type' => 'text', 'settings' => ['content' => 'Свежая обжарка, десерты и кофе с собой каждый день.']],
                    ['type' => 'button', 'settings' => ['text' => 'Записаться на дегустацию', 'url' => '/booking', 'style' => 'primary']],
                ],
            ],
            [
                'id' => 'smoke-faq',
                'settings' => ['padding_top' => 64, 'padding_bottom' => 64],
                'blocks' => [
                    ['type' => 'faq', 'settings' => ['items' => [
                        ['question' => 'Когда вы открыты?', 'answer' => 'Каждый день с 8:00 до 22:00.'],
                        ['question' => 'Есть ли доставка?', 'answer' => 'Да, по городу за 40 минут.'],
                    ]]],
                ],
            ],
        ],
    ],
};

header('Content-Type: application/json');
echo json_encode([
    'id' => 'chatcmpl-smoke',
    'object' => 'chat.completion',
    'choices' => [[
        'index' => 0,
        'message' => ['role' => 'assistant', 'content' => json_encode($answer, JSON_UNESCAPED_UNICODE)],
        'finish_reason' => 'stop',
    ]],
    'usage' => ['prompt_tokens' => 120, 'completion_tokens' => 200, 'total_tokens' => 320],
]);
