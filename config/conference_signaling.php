<?php

return [
    'enabled' => (bool) env('CONFERENCE_SIGNALING_ENABLED', true),

    'server' => [
        'host' => env('CONFERENCE_SIGNALING_HOST', ''),
        'bind_host' => env('CONFERENCE_SIGNALING_BIND_HOST', '127.0.0.1'),
        'port' => (int) env('CONFERENCE_SIGNALING_PORT', 6001),
        'scheme' => env('CONFERENCE_SIGNALING_SCHEME', ''),
        'path' => env('CONFERENCE_SIGNALING_PATH', '/ws/conference'),
    ],

    'token_ttl' => (int) env('CONFERENCE_SIGNALING_TOKEN_TTL', 7200),
];
