<?php

declare(strict_types=1);

namespace App\Module\Account\Domain\Repository;

use App\Module\Account\Domain\Entity\User;

interface UserRepositoryInterface
{
    public function findUserById(string|int $id): ?User;

    public function findUserByEmail(string $email): ?User;

    public function findUserByDocument(string $document): ?User;

    public function save(array $data): ?User;

    public function update(string|int $userId, array $data): bool;
}
