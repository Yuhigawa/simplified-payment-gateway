<?php

declare(strict_types=1);

namespace App\Module\Transaction\Domain\Repository;

interface TransactionRepositoryInterface
{
    public function create(array $data): ?bool;
}
