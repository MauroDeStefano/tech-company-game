<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'Tech Company Game API',
            ],
            'routes' => [
                'api' => 'api/documentation',
            ],
            'paths' => [
                'use_absolute_path' => true,
                'docs_json' => 'api-docs.json',
                'format_to_use_for_docs' => 'json',
                
                // SOLO il file API principale
                'annotations' => [
                    base_path('routes/api.php'),
                ],
                
                // ESCLUDI tutto il resto
                'excludes' => [
                    base_path('app'),
                    base_path('vendor'),
                    base_path('storage'),
                    base_path('tests'),
                    base_path('database'),
                    base_path('bootstrap'),
                    base_path('config'),
                    base_path('public'),
                    base_path('resources'),
                    base_path('routes/api'),
                    base_path('routes/web.php'),
                    base_path('routes/console.php'),
                    base_path('routes/channels.php'),
                ],
            ],
            'generate_always' => true,
            'constants' => [
                'L5_SWAGGER_CONST_HOST' => 'http://localhost:8000',
            ],
        ],
    ],
];