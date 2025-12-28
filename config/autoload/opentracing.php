<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Zipkin\Samplers\BinarySampler;

use function Hyperf\Support\env;

return [
    'default' => env('TRACER_DRIVER', 'zipkin'),
    'enable' => [
        'guzzle' => env('TRACER_ENABLE_GUZZLE', false),
        'redis' => env('TRACER_ENABLE_REDIS', false),
        'db' => env('TRACER_ENABLE_DB', false),
        'method' => env('TRACER_ENABLE_METHOD', false),
    ],
    'tracer' => [
        'zipkin' => [
            'driver' => Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'app' => [
                'name' => env('APP_NAME', 'hyperf'),
                'ipv4' => null,
                'ipv6' => null,
                'port' => 9501,
            ],
            'options' => [
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://zipkin:9411/api/v2/spans'),
                'timeout' => 5,
            ],
            'sampler' => BinarySampler::createAsAlwaysSample(),
        ],
        'jaeger' => [
            'driver' => Hyperf\Tracer\Adapter\JaegerTracerFactory::class,
            'name' => env('APP_NAME', 'skeleton'),
            'options' => [
                'local_agent' => [
                    'reporting_host' => env('JAEGER_REPORTING_HOST', 'localhost'),
                    'reporting_port' => env('JAEGER_REPORTING_PORT', 5775),
                ],
            ],
        ],
    ],
];
