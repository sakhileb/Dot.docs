<?php

return [
    'name' => 'Dot.docs',
    'shortName' => 'Docs',
    'description' => 'Collaborative document editing with AI-powered features',
    'startUrl' => '/dashboard',
    'scope' => '/',
    'display' => 'standalone',
    'orientation' => 'portrait-primary',
    'theme_color' => '#3B82F6',
    'background_color' => '#FFFFFF',

    'categories' => ['productivity', 'business'],

    'screenshots' => [
        [
            'src' => '/images/screenshot-1.png',
            'sizes' => '540x720',
            'type' => 'image/png',
            'form_factor' => 'narrow',
        ],
        [
            'src' => '/images/screenshot-2.png',
            'sizes' => '1280x720',
            'type' => 'image/png',
            'form_factor' => 'wide',
        ],
    ],

    'icons' => [
        [
            'src' => '/images/icon-192x192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => '/images/icon-512x512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => '/images/icon-maskable.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ],
    ],

    'shortcuts' => [
        [
            'name' => 'New Document',
            'short_name' => 'New Doc',
            'description' => 'Create a new document',
            'url' => '/documents/create',
            'icons' => [
                [
                    'src' => '/images/icon-new-doc.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
            ],
        ],
        [
            'name' => 'My Dashboard',
            'short_name' => 'Dashboard',
            'description' => 'View your dashboard',
            'url' => '/my-dashboard',
            'icons' => [
                [
                    'src' => '/images/icon-dashboard.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
            ],
        ],
    ],
];
