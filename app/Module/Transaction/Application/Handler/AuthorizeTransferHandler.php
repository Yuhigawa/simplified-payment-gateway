<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Handler;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Contract\ContainerInterface;
use App\Module\Transaction\Domain\Exception\TransferException;
use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;

class AuthorizeTransferHandler extends AbstractTransferHandler
{
    private const AUTHORIZATION_URL = 'https://util.devi.tools/api/v2/authorize';
    private const TIMEOUT = 5; // segundos

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('default');
    }

    protected function process(TransferContext $context): void
    {
        $client = new Client([
            'timeout' => self::TIMEOUT,
        ]);

        try {
            $response = $client->get(self::AUTHORIZATION_URL);
            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['status']) && $body['status'] === 'success') {
                $context->setAuthorized(true);

                $this->logger->info('[AuthorizeTransfer] - Transfer authorized', [
                    'amount' => $context->getAmountAsFloat(),
                ]);

                return;
            }

            if (isset($body['status']) && $body['status'] === 'fail') {
                $this->logger->warning('[AuthorizeTransfer] - Transfer not authorized by external service', [
                    'response' => $body,
                ]);

                $context->addError('Transfer not authorized');
                throw TransferException::notAuthorized();
            }

            $context->addError('Transfer not authorized');
            throw TransferException::notAuthorized();
        } catch (\Throwable $e) {
            $this->logger->error('[AuthorizeTransfer] - Authorization service error', [
                'error' => $e->getMessage(),
            ]);

            $context->addError('Transfer not authorized');
            throw TransferException::notAuthorized();
        }
    }
}
