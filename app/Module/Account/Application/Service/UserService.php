<?php

declare(strict_types=1);

namespace App\Module\Account\Application\Service;

use Exception;
use Throwable;
use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Context\ApplicationContext;
use App\Module\Account\Domain\Entity\User;
use App\Module\Account\Domain\Exception\UserException;
use App\Module\Account\Infra\Persistence\UserRepository;

class UserService
{
    #[Inject]
    private UserRepository $userRepository;

    #[Inject]
    protected LoggerInterface $logger;

    public function __construct()
    {
        $container = ApplicationContext::getContainer();
        $this->logger = $container->get(LoggerFactory::class)->get('default');
    }

    public function createUser(array $data): User
    {
        // TOOD: if the cache is out of service the users will be created in pgsql but will return error in the api.
        try {
            $user = $this->userRepository->save($data);

            if (!$user) {
                throw new Exception('User was not created.');
            }

            return $user;
        } catch (\Throwable $e) {
            $this->logger->debug('[USER-SERVICE] - createUser', [
                'msg' => $e->getMessage()
            ]);

            throw UserException::createUserException();
        }
    }

    public function findUser(int $userId): User
    {
        try {
            $user = $this->userRepository->findUserById($userId);

            if (!$user) {
                throw new Exception('User not found.');
            }

            return $user;
        } catch (\Throwable $e) {
            $this->logger->debug('[USER-SERVICE] - findUser', [
                'msg' => $e->getMessage()
            ]);

            throw UserException::findUserException();
        }
    }
}
