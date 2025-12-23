<?php

declare(strict_types=1);

namespace App\Module\Account\Domain\ValueObject;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class DocumentType extends AbstractConstants
{
    public const CPF = 'cpf';
    public const CNPJ = 'cnpj';

    public static function getLabels(): array
    {
        return [
            self::CPF => 'CPF',
            self::CNPJ => 'CNPJ',
        ];
    }

    public static function getValues(): array
    {
        return [
            self::CPF,
            self::CNPJ,
        ];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::getValues(), true);
    }
}