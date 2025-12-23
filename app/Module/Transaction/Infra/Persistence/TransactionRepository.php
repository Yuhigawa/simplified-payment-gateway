<?php

declare(strict_types=1);

namespace App\Module\Transaction\Infra\Persistence;

use App\Module\Transaction\Domain\Repository\TransactionRepositoryInterface;
use App\Module\Transaction\Domain\Entity\Transaction;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function create(array $data): ?bool
    {
        $transaction = new Transaction();

        $transaction->fill($data);

        if ($transaction->save()) {
            return true;
        }

        return false;
    }
}
