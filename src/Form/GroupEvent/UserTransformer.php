<?php

declare(strict_types=1);

namespace App\Form\GroupEvent;

use App\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


readonly class UserTransformer implements DataTransformerInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function transform($value): mixed
    {
        return implode(',', $value);
    }

    public function reverseTransform($value): mixed
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
