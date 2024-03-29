<?php

declare(strict_types=1);

namespace App\Form\GroupEvent;

use App\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class UserTransformer implements DataTransformerInterface
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function transform($value)
    {
        return implode(',', $value);
    }

    public function reverseTransform($value)
    {
        if (!$value) {
            return [];
        }

        // Convert the comma-separated string of user IDs to an array
        $userIds = explode(',', $value);

        // Fetch the user objects from the database using the IDs
        $users = $this->userRepository->findBy(['id' => $userIds]);

        // Ensure that all user IDs were found in the database
        if (count($users) !== count($userIds)) {
            throw new TransformationFailedException('Some users were not found.');
        }

        return $users;
    }
}
