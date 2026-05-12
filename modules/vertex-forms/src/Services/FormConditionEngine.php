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

    /**
     * Determine which fields should be visible given current form data.
     */
    public function evaluateFields(array $fields, array $data): array
    {
        $visible = [];

        foreach ($fields as $field) {
            if (!$field["visible"]) continue;

            $cond = $field["conditional"] ?? null;
            if (!$cond) {
                $visible[] = $field["name"];
                continue;
            }

            $dependsOn = $cond["depends_on"] ?? "";
            $operator = $cond["operator"] ?? "equals";
            $value = $cond["value"] ?? "";

            $fieldValue = $data[$dependsOn] ?? null;

            if ($this->evaluate($fieldValue, $operator, $value)) {
                $visible[] = $field["name"];
            }
        }

        return $visible;
    }
}
