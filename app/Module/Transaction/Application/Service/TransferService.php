<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Service;

use Hyperf\Amqp\Producer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Module\Transaction\Domain\Entity\Transaction;
use App\Module\Transaction\Infra\Amqp\TransferProducer;
use App\Module\Transaction\Domain\Exception\TransferException;
use App\Module\Transaction\Application\Handler\TransferContext;
use App\Module\Account\Domain\Repository\UserRepositoryInterface;
use App\Module\Transaction\Application\Factory\TransferHandlerFactory;

class TransferService
{
    #[Inject]
    protected UserRepositoryInterface $userRepository;

    #[Inject]
    protected LoggerFactory $loggerFactory;

    #[Inject]
    protected ContainerInterface $container;

    #[Inject]
    protected Producer $producer;

    #[Inject]
    protected LoggerInterface $logger;

    #[Inject]
    protected TransferHandlerFactory $transferHandlerFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('default');
    }

    public function transfer(array $data): void
    {
        $this->logger->info('[TransferService] - transfer', [
            'data' => $data,
        ]);

        // Balance is integer so we convert cents to full balance
        $data['value'] = $this->normalizarValor($data['value']);
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

        try {
            $producerMessage = new TransferProducer($data);
            $this->producer->produce($producerMessage);

            $this->logger->info('[TransferService] - Transfer request queued successfully', [
                'payer' => $data['payer'] ?? null,
                'payee' => $data['payee'] ?? null,
                'value' => $data['value'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('[TransferService] - Failed to queue transfer request', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw TransferException::queueFailed($e->getMessage());
        }
    }

    public function processTransfer(array $data): ?Transaction
    {
        $this->logger->debug('[TransferService] - processTransfer', [
            'data' => $data,
        ]);

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

        $context = new TransferContext($data['value'], $payer, $payee);

        try {
            $validateUserType = $this->transferHandlerFactory->create();
            $validateUserType->handle($context);

            if ($context->hasErrors()) {
                throw TransferException::validationErrors(implode(', ', $context->getErrors()));
            }

            if (!$context->getTransaction()) {
                throw TransferException::transactionNotCreated();
            }

            return $context->getTransaction();
        } catch (\Throwable $e) {
            $this->logger->error('[TransferService] - Transfer failed', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    // TODO: move to a helper function
    private function normalizarValor(int|float $valor): int
    {
        if (floor($valor) != $valor) {
            return (int) round($valor * 100);
        }

        return (int) $valor;
    }
}
