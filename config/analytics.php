<?php

return [
    'enabled' => (bool) env('ANALYTICS_ENABLED', true),

    // supported: plausible, google
    'provider' => env('ANALYTICS_PROVIDER', 'plausible'),

    'plausible' => [
        'domain' => env('PLAUSIBLE_DOMAIN'),
        'script_url' => env('PLAUSIBLE_SCRIPT_URL', 'https://plausible.io/js/script.js'),
    ],

    'google' => [
        'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID'),
    ],

    'dashboard' => [
        'refresh_seconds' => (int) env('ANALYTICS_DASHBOARD_REFRESH_SECONDS', 60),
    ],
];
