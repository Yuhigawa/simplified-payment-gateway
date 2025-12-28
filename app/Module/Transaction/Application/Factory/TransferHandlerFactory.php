<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Factory;

use Psr\Container\ContainerInterface;
use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;
use App\Module\Transaction\Application\Handler\NotifyPayeeHandler;
use App\Module\Transaction\Application\Handler\ExecuteTransferHandler;
use App\Module\Transaction\Application\Handler\ValidateBalanceHandler;
use App\Module\Transaction\Application\Handler\ValidateUserTypeHandler;
use App\Module\Transaction\Application\Handler\AuthorizeTransferHandler;
use Hyperf\Di\Annotation\Inject;

class TransferHandlerFactory
{
    #[Inject]
    protected ContainerInterface $container;

    public function create(): AbstractTransferHandler
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
