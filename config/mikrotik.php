<?php

return [
    'api_port' => env('MIKROTIK_API_PORT', 8728),
    'api_timeout' => env('MIKROTIK_API_TIMEOUT', 5),
    'heartbeat_interval' => env('MIKROTIK_HEARTBEAT_INTERVAL', 300),
];
