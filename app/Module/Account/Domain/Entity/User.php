<?php

declare(strict_types=1);

namespace App\Module\Account\Domain\Entity;

use App\Model\Model as BaseModel;
use App\Module\Account\Domain\ValueObject\DocumentType;
use App\Module\Account\Domain\ValueObject\Id;
use Hyperf\Database\Model\Events\Creating;

class User extends BaseModel
{   
    protected ?string $connection = 'default';
    protected ?string $table = 'users';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'id',
        'name',
        'email',
        'password',
        'document',
        'document_type',
        'balance',
    ];

    protected array $casts = [
        'id' => 'string',
        'name' => 'string',
        'email' => 'string',
        'document' => 'string',
        'document_type' => 'string',
        'balance' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected array $hidden = ['password'];

    protected ?string $timezone = 'UTC';

    public function creating(Creating $event)
    {
        $this->setAttribute('id', Id::generateSnowFlakeId());
    }

    public function setPasswordAttribute($password): void
    {
        $this->attributes['password'] = password_hash($password, PASSWORD_BCRYPT);;
    }

    public function setEmailAttribute($email): void
    {
        $this->attributes['email'] = strtolower(trim($email));
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return DocumentType::getLabels()[$this->document_type] ?? '';
    }
}