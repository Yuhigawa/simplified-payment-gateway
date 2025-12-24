<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Handler;

use App\Module\Account\Domain\Exception\UserException;
use App\Module\Account\Domain\Repository\UserRepositoryInterface;
use App\Module\Transaction\Domain\Entity\Transaction;
use App\Module\Transaction\Domain\Exception\TransferException;
use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;
use App\Module\Transaction\Domain\Repository\TransactionRepositoryInterface;
use App\Module\Transaction\Domain\ValueObject\TransactionStatus;
use Hyperf\DbConnection\Db;
use Hyperf\Contract\ContainerInterface;

class ExecuteTransferHandler extends AbstractTransferHandler
{
    protected UserRepositoryInterface $userRepository;
    protected TransactionRepositoryInterface $transactionRepository;

    public function __construct(ContainerInterface $container)
    {
        $this->userRepository = $container->get(UserRepositoryInterface::class);
        $this->transactionRepository = $container->get(TransactionRepositoryInterface::class);
    }

    protected function process(TransferContext $context): void
    {
        Db::transaction(function () use ($context) {
            $payer = $context->getPayer();
            $payee = $context->getPayee();
            $fullAmount = $context->getAmount();

            $payer = $this->userRepository->findUserById($payer->id);
            $payee = $this->userRepository->findUserById($payee->id);

            if (!$payer || !$payee) {
                throw TransferException::payerNotFound();
            }

            $newPayerBalance = (int)$payer->balance - $fullAmount;
            $payeUpdated = $this->userRepository->update($payer->id, ['balance' => $newPayerBalance]);

            if (!$payeUpdated) {
                throw UserException::balanceNotUpdateException($payer->id);
            }

            $newPayeeBalance = (int)$payee->balance + $fullAmount;
            $payeeUpdated = $this->userRepository->update($payee->id, ['balance' => $newPayeeBalance]);

            if (!$payeeUpdated) {
                throw UserException::balanceNotUpdateException($payee->id);
            }

            $transaction = new Transaction();
            $transaction->fill([
                'value' => $fullAmount,
                'payer_id' => $payer->id,
                'payee_id' => $payee->id,
                'status' => TransactionStatus::COMPLETED,
            ]);

            if (!$transaction->save()) {
                throw TransferException::transactionNotCreated();
            }

            $context->setTransaction($transaction);
            $context->setExecuted(true);

            $payer->balance = $newPayerBalance;
            $payee->balance = $newPayeeBalance;
            $context->getPayer()->balance = $newPayerBalance;
            $context->getPayee()->balance = $newPayeeBalance;
        });
    }
}
