<?php

namespace App\Tests;

use App\Kernel;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use InvalidArgumentException;
use ReflectionObject;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class IntegrationTestCase extends WebTestCase
{
    protected static ?EntityManagerInterface $entityManager = null;

    protected ?KernelBrowser $client = null;

    private ?ReferenceRepository $referenceRepository = null;

    abstract protected function getFixtures(): array;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        // ✅ This boots the kernel
        $this->client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = self::$entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Drop & recreate schema
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if ($metadata) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }

        // Load fixtures **using the same EM that the client uses**
        $loader = new Loader();
        foreach ($this->getFixtures() as $fixture) {
            $loader->addFixture($fixture);
        }
        $executor = new ORMExecutor($em, new ORMPurger($em));
        $executor->execute($loader->getFixtures(), true);

        // Store reference repository
        $this->referenceRepository = $executor->getReferenceRepository();

        // Clear EM to detach entities
        $em->clear();
    }

    /**
     * @template T of object
     *
     * @param class-string<T>|null $expectedClass
     *
     * @return T|object
     */
    protected function getReference(string $name, ?string $expectedClass = null): object
    {
        if (!$this->referenceRepository) {
            throw new RuntimeException('Reference repository not initialized.');
        }

        // We use reflection because the local version of ReferenceRepository has a restricted API (by class)
        // and we want to find a reference by name across all classes.
        $reflection = new ReflectionObject($this->referenceRepository);
        $prop = $reflection->getProperty('referencesByClass');
        $prop->setAccessible(true);
        $referencesByClass = $prop->getValue($this->referenceRepository);

        foreach ($referencesByClass as $class => $references) {
            if (isset($references[$name])) {
                $reference = $references[$name];
                
                // Ensure we're using a managed instance of the entity
                if (self::$entityManager->contains($reference)) {
                    return $reference;
                }

                $id = self::$entityManager->getClassMetadata(get_class($reference))->getIdentifierValues($reference);

                $entity = self::$entityManager->find(get_class($reference), $id);
                if ($expectedClass && !$entity instanceof $expectedClass) {
                    throw new InvalidArgumentException(sprintf('Reference "%s" is not of type "%s".', $name, $expectedClass));
                }

                return $entity;
            }
        }

        throw new InvalidArgumentException(sprintf('Reference "%s" not found in fixtures.', $name));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (self::$entityManager) {
            self::$entityManager->clear();
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$entityManager) {
            $schemaTool = new SchemaTool(self::$entityManager);
            $metadata = self::$entityManager->getMetadataFactory()->getAllMetadata();

            if ($metadata) {
                $schemaTool->dropSchema($metadata);
            }

            self::$entityManager->close();
            self::$entityManager = null;
        }
    }

    protected function assertJsonContains(array $expected, ?string $json = null): void
    {
        $json = $json ?? $this->client->getResponse()->getContent();
        $data = json_decode($json, true);

        if (!is_array($data)) {
            $this->fail('Response is not valid JSON: ' . $json);
        }

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $data, "Key '$key' not found in JSON response.");
            $this->assertEquals($value, $data[$key], "Value for key '$key' does not match.");
        }
    }

    protected function assertResponseContainsString(string $expected): void
    {
        $this->assertStringContainsString($expected, $this->client->getResponse()->getContent());
    }
}
