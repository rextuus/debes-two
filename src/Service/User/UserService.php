<?php

namespace App\Service\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class UserService
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * UserService constructor.
     *
     * @param UserRepository $userRepository
     * @param UserFactory $userFactory
     */
    public function __construct(UserRepository $userRepository, UserFactory $userFactory)
    {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
    }

    /**
     * storeUser
     *
     * @param UserData $userData
     *
     * @return User
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeUser(UserData $userData): User
    {
        $user = $this->userFactory->createByData($userData);

        $this->userRepository->save($user, true);

        return $user;
    }

    /**
     * @param User $requester
     *
     * @return array
     */
    public function findAllOther(User $requester): array
    {
        $all = $this->userRepository->findAll();
        $key = array_search($requester, $all);
        unset($all[$key]);
        return $all;
    }

    /**
     * findUserByUserName
     *
     * @param string $userName
     *
     * @return User|null
     *
     */
    public function findUserByUserName(string $userName): ?User
    {
        return $this->userRepository->findOneBy(['username' => $userName]);
    }

    public function findUserById(int $userId): ?User
    {
        return $this->userRepository->find($userId);
    }
}
