<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponsePlusInterface $response)
    {
        $this->logger->error(
            sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile())
        );
        $this->logger->error($throwable->getTraceAsString());
        $this->stopPropagation();
        return $response
            ->addHeader('Server', 'Hyperf')
            ->setStatus(500)
            ->setBody(new SwooleStream('Internal Server Error.'));
    }

    public function isValid(Throwable $throwable): bool
    {
        // Don't handle HTTP exceptions - let HttpExceptionHandler handle them
        if ($throwable instanceof \Hyperf\HttpMessage\Exception\HttpException) {
            return false;
        }
        // Don't handle validation exceptions - let ValidationExceptionHandler handle them
        if ($throwable instanceof \Hyperf\Validation\ValidationException) {
            return false;
        }
        return true;
    }
}
