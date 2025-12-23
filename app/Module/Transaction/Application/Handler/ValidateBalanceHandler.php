<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Handler;

use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Di\Annotation\Inject;

use App\Module\Transaction\Domain\Exception\TransferException;
use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;

class ValidateBalanceHandler extends AbstractTransferHandler
{
    #[Inject]
    protected LoggerFactory $loggerFactory;

    private function getLogger(): LoggerInterface
    {
        return $this->loggerFactory->get('default');
    }

    protected function process(TransferContext $context): void
    {
        $payer = $context->getPayer();
        $fullAmount = $context->getAmount();
        $currentBalance = (int) $payer->balance;

        $this->getLogger()->info('[ValidateBalance] - handler', [
            'payer_balance' => $currentBalance,
            'amount' => $fullAmount,
        ]);

        if ($currentBalance < $fullAmount) {
            $context->addError('Insufficient balance');
            throw TransferException::insufficientBalance();
        }
    }
}
