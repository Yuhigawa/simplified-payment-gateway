<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Handler;

use App\Module\Account\Domain\Entity\User;
use App\Module\Transaction\Domain\Entity\Transaction;

class TransferContext
{
    private int $amount;
    private User $payer;
    private User $payee;
    private ?Transaction $transaction = null;
    private bool $authorized = false;
    private bool $executed = false;
    private array $errors = [];

    public function __construct(int $amount, User $payer, User $payee)
    {
        $this->amount = $amount;
        $this->payer = $payer;
        $this->payee = $payee;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getAmountAsFloat(): float
    {
        return $this->amount / 100.0;
    }

    public function getPayer(): User
    {
        return $this->payer;
    }

    public function getPayee(): User
    {
        return $this->payee;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function isAuthorized(): bool
    {
        return $this->authorized;
    }

    public function setAuthorized(bool $authorized): void
    {
        $this->authorized = $authorized;
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    public function setExecuted(bool $executed): void
    {
        $this->executed = $executed;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
