<?php

declare(strict_types=1);

namespace App\Module\Transaction\Infra\Amqp;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;

#[Producer(
    exchange: 'hyperf',
    routingKey: 'hyperf.transfer',
)]
class TransferProducer extends ProducerMessage
{
    public function __construct(array $transferData)
    {
        $this->payload = json_encode($transferData);
    }
}
