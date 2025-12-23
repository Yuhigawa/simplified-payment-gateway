<?php

declare(strict_types=1);

namespace App\Module\Transaction\Presentation\Resource;

use Hyperf\Resource\Json\JsonResource;

use App\Module\Account\Presentation\Resource\UserResource;

class TransferResource extends JsonResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'payer' => new UserResource($this->payer),
            'payee' => new UserResource($this->payee),
            'value' => $this->value,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
