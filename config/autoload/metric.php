<?php

declare(strict_types=1);

use Hyperf\Metric\Adapter\Prometheus\MetricFactory;
use function Hyperf\Support\env;

return [
    'default' => env('METRIC_DRIVER', 'prometheus'),
    'use_standalone_process' => env('METRIC_USE_STANDALONE_PROCESS', false),
    'enable_default_metric' => env('METRIC_ENABLE_DEFAULT_METRIC', true),
    'default_metric_interval' => env('METRIC_DEFAULT_METRIC_INTERVAL', 5),
    'metric' => [
        'prometheus' => [
            'driver' => MetricFactory::class,
            'mode' => Hyperf\Metric\Adapter\Prometheus\Constants::SCRAPE_MODE,
            'namespace' => env('APP_NAME', 'skeleton'),
            'scrape_host' => env('PROMETHEUS_HOST', '0.0.0.0'),
            'scrape_port' => env('PROMETHEUS_PORT', 9501),
            'scrape_path' => '/metrics',
            'push_host' => '0.0.0.0',
            'push_port' => 9091,
            'push_interval' => 5,
        ],
    ],
];
