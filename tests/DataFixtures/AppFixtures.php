<?php

namespace App\Tests\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // --- create a test user ---
        $user = new User();
        $user->setUsername('admin@example.com');
        $user->setFirstName('Peter');
        $user->setLastName('Parker');
        $user->setEmail('admin@example.com');

        // Important: set a dummy hashed password
        // You can hash using bcrypt for Symfony
        $user->setPassword(password_hash('password', PASSWORD_BCRYPT));

        $user->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);


        $manager->flush();
    }
}
