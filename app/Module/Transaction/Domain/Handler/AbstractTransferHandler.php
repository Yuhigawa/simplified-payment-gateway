<?php

declare(strict_types=1);

namespace App\Module\Transaction\Domain\Handler;

use App\Module\Transaction\Application\Handler\TransferContext;

abstract class AbstractTransferHandler implements TransferHandlerInterface
{
    protected ?TransferHandlerInterface $nextHandler = null;

    public function setNext(TransferHandlerInterface $handler): TransferHandlerInterface
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    public function handle(TransferContext $context): void
    {
        $this->process($context);

        if ($this->nextHandler !== null && !$context->hasErrors()) {
            $this->nextHandler->handle($context);
        }
    }

    abstract protected function process(TransferContext $context): void;
}

