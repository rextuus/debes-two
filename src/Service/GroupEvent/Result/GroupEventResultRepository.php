<?php

namespace App\Service\GroupEvent\Result;

use App\Entity\GroupEventResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupEventResult>
 *
 * @method GroupEventResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupEventResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupEventResult[]    findAll()
 * @method GroupEventResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupEventResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupEventResult::class);
    }

    public function save(GroupEventResult $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GroupEventResult $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return GroupEventResult[] Returns an array of GroupEventResult objects
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

//    public function findOneBySomeField($value): ?GroupEventResult
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
