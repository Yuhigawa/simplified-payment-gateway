<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Handler;

use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use function Hyperf\Coroutine\co;

class NotifyPayeeHandler extends AbstractTransferHandler
{
    private const NOTIFICATION_URL = 'https://util.devi.tools/api/v1/notify';
    private const TIMEOUT = 3; // segundos

    #[Inject]
    protected LoggerFactory $loggerFactory;

    private function getLogger(): LoggerInterface
    {
        return $this->loggerFactory->get('default');
    }

    protected function process(TransferContext $context): void
    {
        $this->sendNotificationAsync($context);
    }

    private function sendNotificationAsync(TransferContext $context): void
    {
        $payeeEmail = $context->getPayee()->email;
        $amount = $context->getAmountAsFloat();
        $logger = $this->getLogger();

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

                $logger->info('Notification sent successfully', [
                    'payee_email' => $payeeEmail,
                    'amount' => $amount,
                    'status_code' => $response->getStatusCode(),
                ]);
            } catch (GuzzleException $e) {
                $logger->error('Failed to send notification', [
                    'payee_email' => $payeeEmail,
                    'amount' => $amount,
                    'error' => $e->getMessage(),
                ]);
            } catch (\Throwable $e) {
                $logger->error('Unexpected error sending notification', [
                    'payee_email' => $payeeEmail,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}

