<?php

namespace App\Service\GroupEvent\Payment;

use App\Entity\GroupEvent;
use App\Entity\GroupEventPayment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupEventPayment>
 *
 * @method GroupEventPayment|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupEventPayment|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupEventPayment[]    findAll()
 * @method GroupEventPayment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupEventPaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupEventPayment::class);
    }

    public function save(GroupEventPayment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GroupEventPayment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return GroupEventPayment[] Returns an array of GroupEventPayment objects
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

//    public function findOneBySomeField($value): ?GroupEventPayment
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function persist(GroupEventPayment $groupEventPayment): void
    {
        $this->_em->persist($groupEventPayment);
        $this->_em->flush();
    }
}
