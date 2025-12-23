<?php

declare(strict_types=1);

namespace App\Module\Transaction\Application\Handler;

use App\Module\Account\Domain\ValueObject\DocumentType;
use App\Module\Transaction\Domain\Exception\TransferException;
use App\Module\Transaction\Domain\Handler\AbstractTransferHandler;

class ValidateUserTypeHandler extends AbstractTransferHandler
{
    protected function process(TransferContext $context): void
    {
        $payer = $context->getPayer();

        if ($payer->document_type === DocumentType::CNPJ) {
            $context->addError('Merchants cannot send money');
            throw TransferException::merchantCannotSend();
        }
    }
}
