<?php

declare(strict_types=1);

namespace App\Module\Transaction\Domain\Entity;

use App\Model\Model as BaseModel;
use App\Module\Account\Domain\Entity\User;
use App\Module\Account\Domain\ValueObject\Id;
use Hyperf\Database\Model\Events\Creating;

class Transaction extends BaseModel
{
    protected ?string $connection = 'default';
    protected ?string $table = 'Transfers';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'id',
        'value',
        'payer_id',
        'payee_id',
        'status',
        'created_at',
        'updated_at',
    ];

    protected array $casts = [
        'id' => 'string',
        'value' => 'int',
        'payer_id' => 'string',
        'payee_id' => 'string',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creating(Creating $event)
    {
        $this->setAttribute('id', Id::generateSnowFlakeId());
    }

    public function payer()
    {
        return $this->hasOne(User::class, 'id', 'payer_id');
    }

    public function payee()
    {
        return $this->hasOne(User::class, 'id', 'payee_id');
    }
}
