<?php

namespace Tests\Feature;

use Tests\TestCase;
use Vertex\Forms\Services\FormConditionEngine;

class FormConditionEngineTest extends TestCase
{
    public function test_it_supports_legacy_single_rule_conditions(): void
    {
        $engine = new FormConditionEngine();

        $this->assertTrue($engine->evaluateCondition([
            'depends_on' => 'plan',
            'operator' => 'equals',
            'value' => 'pro',
        ], ['plan' => 'pro']));

        $this->assertFalse($engine->evaluateCondition([
            'depends_on' => 'plan',
            'operator' => 'equals',
            'value' => 'pro',
        ], ['plan' => 'basic']));
    }

    public function test_it_supports_multi_rule_show_and_hide_conditions(): void
    {
        $engine = new FormConditionEngine();

        $condition = [
            'action' => 'show',
            'logic' => 'all',
            'rules' => [
                ['field' => 'plan', 'operator' => 'equals', 'value' => 'pro'],
                ['field' => 'seats', 'operator' => 'greater_than', 'value' => '4'],
            ],
        ];

        $this->assertTrue($engine->evaluateCondition($condition, ['plan' => 'pro', 'seats' => 5]));
        $this->assertFalse($engine->evaluateCondition($condition, ['plan' => 'pro', 'seats' => 2]));

        $condition['action'] = 'hide';

        $this->assertFalse($engine->evaluateCondition($condition, ['plan' => 'pro', 'seats' => 5]));
        $this->assertTrue($engine->evaluateCondition($condition, ['plan' => 'pro', 'seats' => 2]));
    }

    public function test_evaluate_fields_reads_conditions_from_field_options(): void
    {
        $engine = new FormConditionEngine();

        $visible = $engine->evaluateFields([
            ['name' => 'plan', 'visible' => true, 'options' => []],
            [
                'name' => 'company',
                'visible' => true,
                'options' => [
                    'conditional' => [
                        'action' => 'show',
                        'logic' => 'any',
                        'rules' => [
                            ['field' => 'plan', 'operator' => 'equals', 'value' => 'business'],
                            ['field' => 'plan', 'operator' => 'equals', 'value' => 'enterprise'],
                        ],
                    ],
                ],
            ],
        ], ['plan' => 'enterprise']);

        $this->assertSame(['plan', 'company'], $visible);
    }
}
