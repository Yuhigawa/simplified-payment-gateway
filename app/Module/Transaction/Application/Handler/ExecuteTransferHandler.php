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
use Hyperf\Logger\LoggerFactory;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\ContainerInterface;
use Psr\Log\LoggerInterface;

class ExecuteTransferHandler extends AbstractTransferHandler
{
    #[Inject]
    protected UserRepositoryInterface $userRepository;

    #[Inject]
    protected TransactionRepositoryInterface $transactionRepository;

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('default');
    }

    protected function process(TransferContext $context): void
    {
        try {
            Db::transaction(function () use ($context) {
                $payer = $context->getPayer();
                $payee = $context->getPayee();
                $fullAmount = $context->getAmount();

                $payer = $this->userRepository->findUserById($payer->id);
                $payee = $this->userRepository->findUserById($payee->id);

                if (!$payer || !$payee) {
                    $this->logger->error('[ExecuteTransfer] - Payer or payee not found', [
                        'payer' => $payer,
                        'payee' => $payee,
                    ]);

                    throw TransferException::payerNotFound();
                }

                $newPayerBalance = (int)$payer->balance - $fullAmount;
                $payeUpdated = $this->userRepository->update($payer->id, ['balance' => $newPayerBalance]);

                if (!$payeUpdated) {
                    $this->logger->error('[ExecuteTransfer] - Payer balance not updated', [
                        'payer' => $payer,
                        'balance' => $payer->balance,
                        'newBalance' => $newPayerBalance,
                    ]);

                    throw UserException::balanceNotUpdateException($payer->id);
                }

                $newPayeeBalance = (int)$payee->balance + $fullAmount;
                $payeeUpdated = $this->userRepository->update($payee->id, ['balance' => $newPayeeBalance]);

                if (!$payeeUpdated) {
                    $this->logger->error('[ExecuteTransfer] - Payee balance not updated', [
                        'payee' => $payee,
                        'balance' => $payee->balance,
                        'newBalance' => $newPayeeBalance,
                    ]);

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
                    $this->logger->error('[ExecuteTransfer] - Transaction not created', [
                        'payer' => $payer,
                        'payee' => $payee,
                        'value' => $fullAmount,
                    ]);

                    throw TransferException::transactionNotCreated();
                }

                $context->setTransaction($transaction);
                $context->setExecuted(true);

                $payer->balance = $newPayerBalance;
                $payee->balance = $newPayeeBalance;
                $context->getPayer()->balance = $newPayerBalance;
                $context->getPayee()->balance = $newPayeeBalance;
            });
        } catch (\Throwable $e) {
            $this->logger->error('[ExecuteTransfer] - Transaction failed and rollbacked', [
                'payer' => $context->getPayer(),
                'payee' => $context->getPayee(),
                'value' => $context->getAmountAsFloat(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
