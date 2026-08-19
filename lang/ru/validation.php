<?php

return [

    // ── Page renderer — no user-facing strings ─────────────────────────────
    // UI strings live in forms.php

    // ── Form field labels (used by Laravel validator as attribute names) ──
    'attributes' => [
        // Generic field labels used by FormService::validate() via $field->label
    ],

    // ── Custom validation messages ────────────────────────────────────────
    'custom' => [
        'required' => [
            'attribute' => 'forms.validation_required',
        ],
    ],

];
