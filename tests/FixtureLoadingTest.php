<?php

namespace App\Tests;

use App\Entity\User;
use App\Tests\DataFixtures\AppFixtures;

class FixtureLoadingTest extends IntegrationTestCase
{
    protected function getFixtures(): array
    {
        return [new AppFixtures()];
    }

    public function testUserFixtureIsLoaded(): void
    {
        $user = self::$entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        $this->assertNotNull($user);
        $this->assertSame('admin@example.com', $user->getEmail());
    }

    public function testAnotherUserProperty(): void
    {
        $user = self::$entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.com']);

        $this->assertNotEmpty($user->getRoles());
    }
}
