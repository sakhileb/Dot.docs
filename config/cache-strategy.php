<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP Cache Strategy Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching strategies for different types of assets and API responses.
    |
    */

    'static_assets' => [
        'max_age' => 31536000, // 1 year
        'public' => true,
        'immutable' => true,
    ],

    'api_responses' => [
        'documents' => [
            'max_age' => 300, // 5 minutes
            'private' => true,
        ],
        'users' => [
            'max_age' => 600, // 10 minutes
            'private' => true,
        ],
        'teams' => [
            'max_age' => 600, // 10 minutes
            'private' => true,
        ],
    ],

    'html_pages' => [
        'cache' => false,
        'must_revalidate' => true,
    ],

    'cdn' => [
        'enabled' => env('CDN_ENABLED', false),
        'url' => env('CDN_URL', ''),
        'purge_on_update' => true,
    ],

    'compression' => [
        'gzip' => true,
        'brotli' => env('ENABLE_BROTLI', false),
    ],
];
