<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Handler;

use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;

use App\Module\Transaction\Domain\Exception\TransferException;
use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;

class ValidateBalanceHandler extends AbstractTransferHandler
{
    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('default');
    }

    protected function process(TransferContext $context): void
    {
        $payer = $context->getPayer();
        $fullAmount = $context->getAmount();
        $currentBalance = (int) $payer->balance;

        $this->logger->info('[ValidateBalance] - handler', [
            'payer_balance' => $currentBalance,
            'amount' => $fullAmount,
        ]);

        if ($currentBalance < $fullAmount) {
            $context->addError('Insufficient balance');
            throw TransferException::insufficientBalance();
        }
    }
}
