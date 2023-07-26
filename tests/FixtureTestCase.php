<?php


namespace App\Tests;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FixtureTestCase extends WebTestCase
{


    /**
     * @var \Nelmio\Alice\ObjectSet
     */
    private $objectSet;

    protected function setUp(): void
    {
        // boot kernel
        $kernel = self::bootKernel();

        // prepare db
        $doctrineUpdateCommand = sprintf(
            'php bin/console doctrine:schema:update --force > /dev/null'
        );
        exec($doctrineUpdateCommand);
    }

    protected function loadFixtureFiles(array $fixtureFiles): void
    {
        // load fixture file
        $loader = new NativeLoader();
        $this->objectSet = $loader->loadFiles($fixtureFiles);

        $objects = $this->objectSet->getObjects();

        // write fixtures to db
        $container = self::$kernel->getContainer();
        /** @var Registry $repo */
        $repo = $container->get('doctrine');
        $manager = $repo->getManager();
        foreach ($objects as $entity) {
            $manager->persist($entity);
        }
        $manager->flush();
    }

    protected function tearDown(): void
    {
        // clear complete db
        $doctrineDropCommand = sprintf(
            'php bin/console doctrine:database:drop --force --env=test > /dev/null'
        );
        exec($doctrineDropCommand);

        parent::tearDown();
    }


    /**
     * @param string $serviceIdent
     * @return mixed
     */
    protected function getService(string $serviceIdent)
    {
        $container = self::$kernel->getContainer();
        return $container->get($serviceIdent);
    }

    protected function getFixtureEntity(string $class, string $id)
    {
        $container = self::$kernel->getContainer();
        /** @var Registry $repo */
        $repo = $container->get('doctrine');
        $manager = $repo->getManager();
        return $manager->getRepository($class)->find($id);
    }

    protected function getFixtureEntityByIdent(string $ident){
//        dump($this->objectSet->getObjects());
        return $this->objectSet->getObjects()[$ident];
//        $container = self::$kernel->getContainer();
//        /** @var Registry $repo */
//        $repo = $container->get('doctrine');
//        $manager = $repo->getManager();
//        return $manager->getRepository($class)->find($fixture->getId());
    }

    public function refreshLoadedEntity(&$entity, bool $refreshIfNotContained = false): void
    {
        /** @var EntityManager $entityManager */
        $container = self::$kernel->getContainer();
        /** @var Registry $repo */
        $repo = $container->get('doctrine');
        $entityManager = $repo->getManager();

        if (!$entityManager->contains($entity)) {
            $entity = $entityManager->merge($entity);

            if ($refreshIfNotContained) {
                $entityManager->refresh($entity);
            }
        } else {
            $entityManager->refresh($entity);
        }
    }
}