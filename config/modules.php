<?php

return [
    'scan_paths' => [
        base_path('modules'),
    ],

    'core_modules' => [
        [
            'id' => 'vertex-core',
            'name' => 'Core',
            'tier' => 'core',
            'activation' => 'always_on',
            'version' => '0.1.0',
            'providers' => [],
            'dependencies' => [],
        ],
        [
            'id' => 'vertex-auth',
            'name' => 'Auth',
            'tier' => 'core',
            'activation' => 'always_on',
            'version' => '0.1.0',
            'providers' => [],
            'dependencies' => ['vertex-core' => '^0.1'],
        ],
        [
            'id' => 'vertex-content',
            'name' => 'Content',
            'tier' => 'core',
            'activation' => 'always_on',
            'version' => '0.1.0',
            'providers' => [],
            'dependencies' => ['vertex-core' => '^0.1', 'vertex-auth' => '^0.1'],
        ],
    ],

    'tiers' => [
        'core' => [
            'label' => 'Core',
            'install_strategy' => 'bundled',
            'activation' => 'always_on',
        ],
        'builtin' => [
            'label' => 'Builtin',
            'install_strategy' => 'bundled',
            'activation' => 'admin_toggle',
        ],
        'marketplace' => [
            'label' => 'Marketplace',
            'install_strategy' => 'external_package',
            'activation' => 'install_then_toggle',
        ],
    ],
];
