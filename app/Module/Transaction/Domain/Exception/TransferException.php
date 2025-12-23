<?php

declare(strict_types=1);

namespace App\Module\Transaction\Domain\Exception;

use Hyperf\HttpMessage\Exception\BadRequestHttpException;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Exception\UnprocessableEntityHttpException;

class TransferException extends HttpException
{
    public static function merchantCannotSend(): BadRequestHttpException
    {
        return new BadRequestHttpException('Merchants cannot send money');
    }

    public static function insufficientBalance(): BadRequestHttpException
    {
        return new BadRequestHttpException('Insufficient balance');
    }

    public static function notAuthorized(): BadRequestHttpException
    {
        return new BadRequestHttpException('Transfer not authorized');
    }

    public static function payerNotFound(): UnprocessableEntityHttpException
    {
        return new UnprocessableEntityHttpException('Payer not found');
    }

    public static function payeeNotFound(): UnprocessableEntityHttpException
    {
        return new UnprocessableEntityHttpException('Payee not found');
    }

    public static function transactionNotCreated(): BadRequestHttpException
    {
        return new BadRequestHttpException('Transaction was not created');
    }

    public static function validationErrors(string $errors): UnprocessableEntityHttpException
    {
        return new UnprocessableEntityHttpException($errors);
    }
}

