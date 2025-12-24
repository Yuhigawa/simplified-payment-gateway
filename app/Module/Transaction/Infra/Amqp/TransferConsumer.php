<?php

declare(strict_types=1);

namespace App\Module\Transaction\Infra\Amqp;

use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Amqp\Result;
use Psr\Log\LoggerInterface;

use App\Module\Account\Domain\Repository\UserRepositoryInterface;
use App\Module\Transaction\Application\Service\TransferService;

#[Consumer(
    exchange: 'hyperf',
    routingKey: 'hyperf.transfer',
    queue: 'hyperf.transfer.queue',
    name: 'TransferConsumer',
    nums: 1,
    enable: true
)]
class TransferConsumer extends ConsumerMessage
{
    #[Inject]
    protected TransferService $transferService;

    #[Inject]
    protected UserRepositoryInterface $userRepository;

    #[Inject]
    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('default');
    }

    public function consumeMessage($data, \PhpAmqpLib\Message\AMQPMessage $message): Result
    {
        try {
            $this->logger->info('[TransferConsumer] - Received transfer request', [
                'data' => $data,
            ]);

            $transferData = json_decode($data, true);

            $payer = $this->userRepository->findUserById($transferData['payer']);
            $payee = $this->userRepository->findUserById($transferData['payee']);

            if (!isset($transferData['value']) || !$payer || !$payee) {
                $this->logger->error('[TransferConsumer] - Invalid transfer data', [
                    'data' => $transferData,
                ]);
                return Result::DROP;
            }

            $transaction = $this->transferService->processTransfer($transferData);

            $this->logger->info('[TransferConsumer] - Transfer processed', [
                'transaction_id' => $transaction?->id,
                'payer' => $transferData['payer'],
                'payee' => $transferData['payee'],
                'value' => $transferData['value'],
            ]);

            return Result::ACK;
        } catch (\Exception $e) {
            $this->logger->error('[TransferConsumer] - Error processing transfer, dropping', [
                'error' => $e->getMessage(),
                'data' => $data ?? null,
            ]);

            return Result::DROP;
        }
    }
}
