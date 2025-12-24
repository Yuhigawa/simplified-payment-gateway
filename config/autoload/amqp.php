<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'default' => [
        'host' => env('RABBITMQ_HOST', 'rabbitmq'),
        'port' => (int) env('RABBITMQ_PORT', 5672),
        'user' => env('RABBITMQ_USER', 'hyperf_user'),
        'password' => env('RABBITMQ_PASSWORD', 'hyperf_password'),
        'vhost' => env('RABBITMQ_VHOST', '/'),
        'concurrent' => [
            'limit' => 1,
        ],
        'pool' => [
            'connections' => 2,
        ],
        'params' => [
            'insist' => false,
            'keepalive' => true,
            'heartbeat' => 3,
            'max_idle_channels' => 10,
        ]
    ],
];
