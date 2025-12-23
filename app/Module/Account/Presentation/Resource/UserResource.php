<?php

declare(strict_types=1);

namespace App\Module\Account\Presentation\Resource;

use Hyperf\Resource\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(): array
    {
        $balance = $this->balance ?? 0;
        $decimalBalance = number_format($balance / 100, 2, '.', '');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'document' => $this->document,
            'document_type' => $this->document_type,
            'balance' => $decimalBalance,
        ];
    }
}
