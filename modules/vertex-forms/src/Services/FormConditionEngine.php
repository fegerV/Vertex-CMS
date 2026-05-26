<?php

namespace Vertex\Forms\Services;

class FormConditionEngine
{
    /**
     * Evaluate a condition: does $fieldValue satisfy $operator == $targetValue?
     */
    public function evaluate($fieldValue, string $operator, $targetValue): bool
    {
        return match ($operator) {
            "equals" => $fieldValue == $targetValue,
            "not_equals" => $fieldValue != $targetValue,
            "contains" => is_string($fieldValue) && str_contains((string)$fieldValue, (string)$targetValue),
            "greater_than" => is_numeric($fieldValue) && (float)$fieldValue > (float)$targetValue,
            "less_than" => is_numeric($fieldValue) && (float)$fieldValue < (float)$targetValue,
            "is_empty" => empty($fieldValue),
            "is_not_empty" => !empty($fieldValue),
            default => true,
        };
    }

    public function evaluateCondition(?array $condition, array $data): bool
    {
        if (empty($condition)) {
            return true;
        }

        $rules = $condition['rules'] ?? null;

        if (!is_array($rules)) {
            $rules = [[
                'field' => $condition['depends_on'] ?? '',
                'operator' => $condition['operator'] ?? 'equals',
                'value' => $condition['value'] ?? '',
            ]];
        }

        $rules = array_values(array_filter($rules, fn ($rule) => !empty($rule['field'] ?? null)));

        if ($rules === []) {
            return true;
        }

        $logic = ($condition['logic'] ?? 'all') === 'any' ? 'any' : 'all';
        $matches = array_map(function (array $rule) use ($data): bool {
            $fieldName = (string) ($rule['field'] ?? '');
            $operator = (string) ($rule['operator'] ?? 'equals');
            $value = $rule['value'] ?? '';

            return $this->evaluate($data[$fieldName] ?? null, $operator, $value);
        }, $rules);

        $passed = $logic === 'any'
            ? in_array(true, $matches, true)
            : !in_array(false, $matches, true);

        return ($condition['action'] ?? 'show') === 'hide' ? !$passed : $passed;
    }

    /**
     * Determine which fields should be visible given current form data.
     */
    public function evaluateFields(array $fields, array $data): array
    {
        $visible = [];

        foreach ($fields as $field) {
            if (!$field["visible"]) continue;

            $cond = $field["conditional"] ?? $field["options"]["conditional"] ?? null;
            if (!$cond || $this->evaluateCondition(is_array($cond) ? $cond : null, $data)) {
                $visible[] = $field["name"];
            }
        }

        return $visible;
    }
}
