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

return [
    // Account Module
    \App\Module\Account\Domain\Repository\UserRepositoryInterface::class =>
        \App\Module\Account\Infra\Persistence\UserRepository::class,

    // Transfer Module
    \App\Module\Transaction\Domain\Repository\TransactionRepositoryInterface::class =>
        \App\Module\Transaction\Infra\Persistence\TransactionRepository::class,

    // // Shared Services
    // \App\Shared\Application\Service\IdGeneratorService::class =>
    //     \App\Shared\Application\Service\IdGeneratorService::class,
];
