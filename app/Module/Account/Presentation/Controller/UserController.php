<?php

declare(strict_types=1);

namespace App\Module\Account\Presentation\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;

use App\Module\Account\Application\Service\UserService;
use App\Module\Account\Presentation\Resource\UserResource;
use App\Module\Account\Presentation\Request\CreateUserRequest;


#[Controller]
class UserController
{
    #[Inject]
    protected readonly UserService $userService;

    public function store(CreateUserRequest $request): UserResource
    {
        $data = $request->validated();

        $user = $this->userService->createUser($data);

        return UserResource::make($user);
    }

    public function show(string $userId): UserResource
    {
        $user = $this->userService->findUser((int)$userId);

        return UserResource::make($user);
    }
}