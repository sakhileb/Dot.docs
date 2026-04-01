<?php

return [
    'enabled' => env('OFFLINE_SUPPORT_ENABLED', true),
    'storage_key' => 'offline_documents',
    'sync_interval' => 30000, // milliseconds
    'max_offline_documents' => 20,
];
