<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Handler;

use App\Module\Transaction\Domain\Exception\TransferException;
use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class AuthorizeTransferHandler extends AbstractTransferHandler
{
    private const AUTHORIZATION_URL = 'https://util.devi.tools/api/v2/authorize';
    private const TIMEOUT = 5; // segundos

    #[Inject]
    protected LoggerFactory $loggerFactory;

    private function getLogger(): LoggerInterface
    {
        return $this->loggerFactory->get('default');
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

                $this->getLogger()->info('Transfer authorized', [
                    'amount' => $context->getAmountAsFloat(),
                ]);

                return;
            }

            if (isset($body['status']) && $body['status'] === 'fail') {
                $this->getLogger()->warning('Transfer not authorized by external service', [
                    'response' => $body,
                ]);

                $context->addError('Transfer not authorized');
                throw TransferException::notAuthorized();
            }

            $context->addError('Transfer not authorized');
            throw TransferException::notAuthorized();
        } catch (GuzzleException $e) {
            $this->getLogger()->error('Authorization service error', [
                'error' => $e->getMessage(),
            ]);

            $context->addError('Transfer not authorized');
            throw TransferException::notAuthorized();
        }
    }
}
