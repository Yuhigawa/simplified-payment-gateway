<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Handler;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Contract\ContainerInterface;
use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;

use function Hyperf\Coroutine\co;

class NotifyPayeeHandler extends AbstractTransferHandler
{
    private const NOTIFICATION_URL = 'https://util.devi.tools/api/v1/notify';
    private const TIMEOUT = 3; // segundos

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('default');
    }

    protected function process(TransferContext $context): void
    {
        $this->sendNotificationAsync($context);
    }

    private function sendNotificationAsync(TransferContext $context): void
    {
        $payeeEmail = $context->getPayee()->email;
        $amount = $context->getAmountAsFloat();
        $logger = $this->logger;

        co(function () use ($payeeEmail, $amount, $logger) {
            try {
                $client = new Client([
                    'timeout' => self::TIMEOUT,
                ]);

                $response = $client->post(self::NOTIFICATION_URL, [
                    'json' => [
                        'email' => $payeeEmail,
                        'message' => sprintf('You received $%.2f', $amount),
                    ],
                ]);

                $logger->info('[NotifyPayee] - Notification sent successfully', [
                    'payee_email' => $payeeEmail,
                    'amount' => $amount,
                    'status_code' => $response->getStatusCode(),
                ]);
            } catch (\Throwable $e) {
                $logger->error('[NotifyPayee] - Unexpected error sending notification', [
                    'payee_email' => $payeeEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}
