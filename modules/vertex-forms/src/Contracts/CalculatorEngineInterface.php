<?php

namespace Vertex\Forms\Contracts;

interface CalculatorEngineInterface
{
    /**
     * Evaluate a mathematical formula safely.
     *
     * @param string $formula Formula with placeholders like {field_name}
     * @param array $values Field values (field_name => value)
     * @param array $dependsOn List of field dependencies
     * @return float Calculated result
     * @throws \InvalidArgumentException If formula is invalid
     */
    public function evaluate(string $formula, array $values, array $dependsOn = []): float;
}
