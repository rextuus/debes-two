<?php

namespace App\Service\User;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{

    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordEncoder;

    /**
     * UserFactory constructor.
     */
    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * createByData
     *
     * @param UserData $userData
     *
     * @return User
     */
    public function createByData(UserData $userData): User
    {
        $user = $this->createNewUserInstance();
        $this->mapData($user, $userData);

        return $user;
    }

    /**
     * mapData
     *
     * @param User $user
     * @param UserData $data
     *
     * @return void
     */
    public function mapData(User $user, UserData $data): void
    {
        $user->setUsername($data->getUserName());
        $user->setEmail($data->getEmail());
        $user->setFirstName($data->getFirstName());
        $user->setLastName($data->getLastName());
        $user->setPassword($this->passwordEncoder->hashPassword($user, $data->getPassword()));
    }

    /**
     * createNewUserInstance
     *
     * @return User
     */
    private function createNewUserInstance(): User
    {
        return new User();
    }
}
