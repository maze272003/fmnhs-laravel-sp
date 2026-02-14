<?php

return [
    'redis_response_cache_enabled' => env('REDIS_RESPONSE_CACHE_ENABLED', true),
    'redis_response_cache_ttl' => (int) env('REDIS_RESPONSE_CACHE_TTL', 30),
    'redis_response_cache_except' => [
        'up',
        'admin/login',
        'student/login',
        'teacher/login',
        'parent/login',
        'logout',
        'admin/*/resolve',
    ],
];
