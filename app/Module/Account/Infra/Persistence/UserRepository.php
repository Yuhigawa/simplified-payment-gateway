<?php

declare(strict_types=1);

namespace App\Module\Account\Infra\Persistence;

use Hyperf\DbConnection\Db;

use App\Module\Account\Domain\Entity\User;
use App\Module\Account\Domain\Repository\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    private const TABLE_NAME = 'users';

    private const ID_COLUMN = 'id';
    private const EMAIL_COLUMN = 'email';
    private const DOCUMENT_COLUMN = 'document';

    private function find($value, $column, $statement = '='): ?User
    {
        $result = DB::table(self::TABLE_NAME)
            ->where($column, $statement, $value)
            ->first();

        if (!$result) {
            return null;
        }

        $user = new User();
        $user->setRawAttributes((array) $result, true);
        return $user;
    }

    public function findUserById(int $id): ?User
    {
        return $this->find($id, self::ID_COLUMN);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->find($email, self::EMAIL_COLUMN);
    }

    public function findUserByDocument(string $document): ?User
    {
        return $this->find($document, self::DOCUMENT_COLUMN);
    }

    public function save(array $data): ?User
    {
        $user = new User();

        $user->fill($data);

        if ($user->save()) {
            return $user;
        }

        return null;
    }
}
