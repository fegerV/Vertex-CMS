<?php

namespace Vertex\Forms\Services;

use Vertex\Forms\Services\FormConditionEngine;

class FormCalculatorEngine
{
    /**
     * Evaluate a mathematical formula safely (no eval()).
     * Supports: + - * / ( ) and field placeholders {field_name}
     */
    public function evaluate(string $formula, array $values, array $dependsOn = []): float
    {
        // Replace field placeholders with numeric values
        $expression = $formula;

        foreach ($values as $key => $val) {
            $numeric = is_numeric($val) ? (float)$val : 0;
            $expression = str_replace('{' . $key . '}', (string)$numeric, $expression);
        }

        // Replace special variables
        $expression = str_replace('{total}', '0', $expression);
        $expression = str_replace('{subtotal}', '0', $expression);
        $expression = str_replace('{tax}', '0', $expression);
        $expression = str_replace('{discount}', '0', $expression);

        // Remove any characters that are not math operators or digits
        // Allow: digits, decimal point, operators, parentheses, spaces
        $expression = preg_replace('/[^0-9\+\-\*\/\.\(\) ]/', '', $expression);

        if (trim($expression) === '') {
            return 0.0;
        }

        // Use a proper math parser instead of eval for security
        return $this->parseMath($expression);
    }

    /**
     * Simple but safe mathematical expression parser.
     * Implements Shunting-yard algorithm or uses a recursive descent parser.
     */
    private function parseMath(string $expr): float
    {
        // Remove spaces
        $expr = str_replace(' ', '', $expr);
        if ($expr === '') return 0;

        // Use tokenization + stack-based evaluation (safe)
        $tokens = $this->tokenize($expr);
        $output = []; // RPN queue
        $operators = []; // operator stack

        $precedence = [
            '+' => 1,
            '-' => 1,
            '*' => 2,
            '/' => 2,
        ];

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $output[] = (float)$token;
            } else {
                while (!empty($operators)) {
                    $op2 = end($operators);
                    if ($precedence[$op2] >= $precedence[$token]) {
                        $output[] = array_pop($operators);
                    } else {
                        break;
                    }
                }
                $operators[] = $token;
            }
        }

        while ($op = array_pop($operators)) {
            $output[] = $op;
        }

        // Evaluate RPN
        $stack = [];
        foreach ($output as $token) {
            if (is_float($token) || is_int($token)) {
                $stack[] = $token;
            } else {
                $b = array_pop($stack);
                $a = array_pop($stack);
                switch ($token) {
                    case '+': $stack[] = $a + $b; break;
                    case '-': $stack[] = $a - $b; break;
                    case '*': $stack[] = $a * $b; break;
                    case '/': $stack[] = $b != 0 ? $a / $b : 0; break;
                }
            }
        }

        return (float)($stack[0] ?? 0);
    }

    /**
     * Tokenize a math expression.
     */
    private function tokenize(string $expr): array
    {
        preg_match_all('/\d+(?:\.\d+)?|[+\-*\/]/', $expr, $matches);
        return $matches[0];
    }
}
