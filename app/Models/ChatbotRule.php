<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotRule extends Model
{
    use HasFactory;

    protected $table = 'chatbot_rules';

    protected $fillable = [
        'chatbot_id',
        'name',
        'description',
        'event_type',
        'conditions',
        'actions',
        'priority',
        'stop_on_match',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'priority' => 'integer',
        'stop_on_match' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Получить бота, которому принадлежит правило
     */
    public function chatbot(): BelongsTo
    {
        return $this->belongsTo(Chatbot::class);
    }

    /**
     * Проверить выполнение условий для сообщения
     */
    public function matchesConditions(string $message, array $context = []): bool
    {
        if (empty($this->conditions)) {
            return true; // Нет условий - правило всегда срабатывает
        }

        foreach ($this->conditions as $condition) {
            $field = $condition['field'] ?? 'message';
            $operator = $condition['operator'] ?? 'contains';
            $value = $condition['value'] ?? '';

            // Получаем значение поля
            $fieldValue = $this->getFieldValue($field, $message, $context);

            // Проверяем условие
            if (!$this->checkCondition($fieldValue, $operator, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Получить значение поля из сообщения или контекста
     */
    private function getFieldValue(string $field, string $message, array $context): mixed
    {
        return match ($field) {
            'message' => $message,
            default => $context[$field] ?? null,
        };
    }

    /**
     * Проверить одно условие
     */
    private function checkCondition(mixed $fieldValue, string $operator, string $value): bool
    {
        if ($fieldValue === null) {
            return false;
        }

        return match ($operator) {
            'equals' => $fieldValue === $value,
            'not_equals' => $fieldValue !== $value,
            'contains' => stripos((string)$fieldValue, $value) !== false,
            'not_contains' => stripos((string)$fieldValue, $value) === false,
            'starts_with' => str_starts_with((string)$fieldValue, $value),
            'ends_with' => str_ends_with((string)$fieldValue, $value),
            'regex' => (bool) preg_match($value, (string)$fieldValue),
            'is_empty' => empty($fieldValue),
            'is_not_empty' => !empty($fieldValue),
            'greater_than' => (float)$fieldValue > (float)$value,
            'less_than' => (float)$fieldValue < (float)$value,
            default => false,
        };
    }

    /**
     * Выполнить действия правила
     * Возвращает результат выполнения и данные для ответа
     */
    public function executeActions(string $message, array $context = []): array
    {
        if (empty($this->actions)) {
            return ['handled' => false, 'response' => null, 'data' => []];
        }

        $result = [
            'handled' => false,
            'response' => null,
            'data' => [],
            'webhook_response' => null,
            'form_schema' => null,
            'should_block_llm' => false,
        ];

        foreach ($this->actions as $action) {
            $type = $action['type'] ?? null;

            if ($type === 'webhook') {
                $webhookResult = $this->executeWebhookAction($action, $message, $context);
                $result['handled'] = true;
                $result['webhook_response'] = $webhookResult;
                
                if (!empty($webhookResult['response'])) {
                    $result['response'] = $webhookResult['response'];
                }
            } elseif ($type === 'show_form') {
                $result['handled'] = true;
                $result['form_schema'] = $action['form_schema'] ?? null;
                $result['should_block_llm'] = true;
            } elseif ($type === 'block_llm') {
                $result['handled'] = true;
                $result['response'] = $action['response'] ?? 'Ваш запрос обрабатывается.';
                $result['should_block_llm'] = true;
            } elseif ($type === 'set_context') {
                // Установить переменные контекста для последующих сообщений
                $result['data']['context_updates'] = $action['variables'] ?? [];
            }
        }

        return $result;
    }

    /**
     * Выполнить webhook действие
     */
    private function executeWebhookAction(array $action, string $message, array $context = []): array
    {
        $url = $action['url'] ?? null;
        $method = $action['method'] ?? 'POST';
        $headers = $action['headers'] ?? ['Content-Type' => 'application/json'];
        
        if (!$url) {
            return ['success' => false, 'error' => 'Webhook URL not specified'];
        }

        // Формируем payload для отправки в n8n
        $payload = array_merge([
            'chatbot_id' => $this->chatbot_id,
            'rule_id' => $this->id,
            'rule_name' => $this->name,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ], $context);

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
                ->timeout(30)
                ->send($method, $url, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'response' => $responseData['response'] ?? $responseData['answer'] ?? null,
                    'data' => $responseData,
                ];
            }

            return [
                'success' => false,
                'error' => "Webhook returned status {$response->status()}",
            ];

        } catch (\Exception $e) {
            \Log::error("Chatbot webhook error: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Проверить, активен ли правило
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
