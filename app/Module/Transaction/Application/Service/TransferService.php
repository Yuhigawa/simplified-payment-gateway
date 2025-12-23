<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Service;

use App\Module\Account\Domain\Repository\UserRepositoryInterface;
use App\Module\Transaction\Application\Handler\AuthorizeTransferHandler;
use App\Module\Transaction\Application\Handler\ExecuteTransferHandler;
use App\Module\Transaction\Application\Handler\NotifyPayeeHandler;
use App\Module\Transaction\Application\Handler\TransferContext;
use App\Module\Transaction\Application\Handler\ValidateBalanceHandler;
use App\Module\Transaction\Application\Handler\ValidateUserTypeHandler;
use App\Module\Transaction\Domain\Exception\TransferException;
use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;
use App\Module\Transaction\Domain\Entity\Transaction;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class TransferService
{
    #[Inject]
    protected UserRepositoryInterface $userRepository;

    #[Inject]
    protected LoggerFactory $loggerFactory;

    #[Inject]
    protected ContainerInterface $container;

    private function getLogger(): LoggerInterface
    {
        return $this->loggerFactory->get('default');
    }

    public function transfer(array $data): Transaction
    {
        // Balance is integer so we convert cents to full balance
        $amount = $this->normalizarValor($data['value']);
        $payerId = $data['payer'];
        $payeeId = $data['payee'];

        $payer = $this->userRepository->findUserById($payerId);
        $payee = $this->userRepository->findUserById($payeeId);

        if (!$payer) {
            throw TransferException::payerNotFound();
        }

        if (!$payee) {
            throw TransferException::payeeNotFound();
        }

        $this->getLogger()->debug('[TransferService] - transfer', [
            'payer_balance' => $payer->balance,
            'payee_balance' => $payee->balance,
        ]);

        $context = new TransferContext($amount, $payer, $payee);

        try {
            $validateUserType = $this->startHandler();
            $validateUserType->handle($context);

            if ($context->hasErrors()) {
                throw TransferException::validationErrors(implode(', ', $context->getErrors()));
            }

            if (!$context->getTransaction()) {
                throw TransferException::transactionNotCreated();
            }

            return $context->getTransaction();
        } catch (\Throwable $e) {
            $this->getLogger()->error('Transfer failed', [
                'payer_id' => $payerId,
                'payer_balance' => $payer->balance,
                'payee_id' => $payeeId,
                'payee_balance' => $payee->balance,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    // TODO: send to helper function
    private function normalizarValor(int|float $valor): int
    {
        if (floor($valor) != $valor) {
            return (int) round($valor * 100);
        }

        return (int) $valor;
    }

    private function startHandler(): AbstractTransferHandler
    {
        $validateUserType = $this->container->get(ValidateUserTypeHandler::class);
        $validateBalance = $this->container->get(ValidateBalanceHandler::class);
        $authorizeTransfer = $this->container->get(AuthorizeTransferHandler::class);
        $executeTransfer = $this->container->get(ExecuteTransferHandler::class);
        $notifyPayee = $this->container->get(NotifyPayeeHandler::class);

        $validateUserType
            ->setNext($validateBalance)
            ->setNext($authorizeTransfer)
            ->setNext($executeTransfer)
            ->setNext($notifyPayee);

        return $validateUserType;
    }
}
