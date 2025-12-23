<?php

declare(strict_types=1);

namespace App\Module\Transaction\Domain\ValueObject;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class TransactionStatus extends AbstractConstants
{
    public const PENDING = 'pending';
    public const COMPLETED = 'completed';
    public const FAILED = 'failed';

    public static function getLabels(): array
    {
        return [
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        ];
    }

    public static function getValues(): array
    {
        return [
            self::PENDING,
            self::COMPLETED,
            self::FAILED,
        ];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::getValues(), true);
    }
}
