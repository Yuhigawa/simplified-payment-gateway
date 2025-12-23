<?php

declare(strict_types=1);

return [
    'modules' => [
        'account' => [
            'enabled' => true,
            'namespace' => 'App\\Module\\Account',
            'path' => BASE_PATH . '/app/Module/Account',
        ],
        'transaction' => [
            'enabled' => true,
            'namespace' => 'App\\Module\\Transaction',
            'path' => BASE_PATH . '/app/Module/Transaction',
            // 'depends_on' => ['account'],
        ],
    ],
];