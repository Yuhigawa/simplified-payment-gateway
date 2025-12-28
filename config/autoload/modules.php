<?php

declare(strict_types=1);

return [
    'modules' => [
        'account' => [
            'enabled' => true,
            'namespace' => 'App\\Module\\Account',
            'path' => BASE_PATH . '/app/Module/Account',
        ],
        'Transfer' => [
            'enabled' => true,
            'namespace' => 'App\\Module\\Transfer',
            'path' => BASE_PATH . '/app/Module/Transfer',
            // 'depends_on' => ['account'],
        ],
    ],
];
