<?php

declare(strict_types=1);

namespace App\Module\Transaction\Domain\Handler;

use App\Module\Transaction\Application\Handler\TransferContext;

interface TransferHandlerInterface
{
    public function handle(TransferContext $context): void;

    public function setNext(TransferHandlerInterface $handler): TransferHandlerInterface;
}

