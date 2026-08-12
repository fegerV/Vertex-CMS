<?php

namespace App\Automation\Services;

use InvalidArgumentException;

class AutomationEngine
{
    public function run(array $workflow, array $context, callable $executor): array
    {
        $steps = $workflow['steps'] ?? [];
        if (! is_array($steps) || count($steps) > config('platform-modules.automation.max_steps', 50)) {
            throw new InvalidArgumentException('Invalid automation workflow.');
        }
        $results = [];
        foreach ($steps as $index => $step) {
            if (! is_array($step) || ! isset($step['type'])) {
                throw new InvalidArgumentException("Invalid automation step {$index}.");
            }
            if (! $this->matches($step['when'] ?? [], $context)) {
                continue;
            }
            $results[] = $executor($step['type'], $step['config'] ?? [], $context);
        }

        return $results;
    }

    private function matches(array $conditions, array $context): bool
    {
        foreach ($conditions as $key => $expected) {
            if (data_get($context, $key) !== $expected) {
                return false;
            }
        }

        return true;
    }
}
