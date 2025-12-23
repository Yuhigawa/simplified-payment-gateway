<?php

declare(strict_types=1);

namespace App\Module\Account\Domain\Exception;

use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Exception\NotFoundHttpException;
use Hyperf\HttpMessage\Exception\UnprocessableEntityHttpException;
use Throwable;

class UserException extends HttpException
{
    public function __construct(string $message = '', int $code = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function createUserException(): UnprocessableEntityHttpException
    {
        return new UnprocessableEntityHttpException('Não foi possível criar o usuário.');
    }

    public static function findUserException(): NotFoundHttpException
    {
        return new NotFoundHttpException('Não foi possível encontrar o usuário.');
    }
}
