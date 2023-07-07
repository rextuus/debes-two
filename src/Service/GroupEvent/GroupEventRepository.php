<?php

namespace App\Service\GroupEvent;

use App\Entity\GroupEvent;
use App\Entity\GroupEventPayment;
use App\Entity\GroupEventUserCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupEvent>
 *
 * @method GroupEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupEvent[]    findAll()
 * @method GroupEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupEvent::class);
    }

    public function save(GroupEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GroupEvent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return GroupEvent[] Returns an array of GroupEvent objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GroupEvent
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function persist(GroupEvent $groupEvent): void
    {
        $this->_em->persist($groupEvent);
        $this->_em->flush();
    }

    public function getTotalSumOfEvent(GroupEvent $groupEvent): float
    {
        try {
            return $this->createQueryBuilder('e')
                ->select('sum(p.amount) as result')
                ->innerJoin(GroupEventPayment::class, 'p', 'WITH', 'p.groupEvent = e.id')
                ->where('e = :event')
                ->setParameter('event', $groupEvent)
                ->orderBy('e.created', 'ASC')
                ->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0.0;
        }
    }
}
