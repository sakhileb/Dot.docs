<?php

return [
    'enabled' => (bool) env('WEBHOOKS_ENABLED', true),
    'timeout_seconds' => (int) env('WEBHOOKS_TIMEOUT_SECONDS', 10),
];
