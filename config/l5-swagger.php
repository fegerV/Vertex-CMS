<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'VertexCMS API Documentation',
                'description' => 'Complete API documentation for VertexCMS including E-commerce, Security, and System endpoints.',
                'version' => '1.0.0',
            ],

            'routes' => [
                /*
                 * Route for accessing parsed swagger annotations.
                 */
                'api' => 'api/documentation',
                /*
                 * Route for accessing the swagger-ui view.
                 */
                'docs' => 'api/docs',
            ],

            'paths' => [
                /*
                 * Edit to include full absolute path or keep null for storage path.
                 */
                'docs' => storage_path('api-docs'),
                'views' => base_path('resources/views/vendor/l5-swagger'),
            ],
        ],
    ],
    'defaults' => [
        'routes' => [
            'docs' => '/api/docs',
            'oauth2_callback' => '/api/oauth2-callback',
        ],
        'middleware' => [
            'api' => [],
            'asset' => [],
            'docs' => [],
            'oauth2_callback' => [],
        ],
        'paths' => [
            'annotations' => [
                base_path('app'),
                base_path('routes'),
            ],
            'docs' => storage_path('api-docs'),
            'views' => base_path('resources/views/vendor/l5-swagger'),
        ],
    ],
];
