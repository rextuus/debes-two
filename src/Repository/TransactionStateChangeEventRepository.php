<?php

namespace App\Repository;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionStateChangeEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TransactionStateChangeEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionStateChangeEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionStateChangeEvent[]    findAll()
 * @method TransactionStateChangeEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionStateChangeEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionStateChangeEvent::class);
    }

    public function persist(TransactionStateChangeEvent $transactionStateChangeEvent): void
    {
        $this->getEntityManager()->persist($transactionStateChangeEvent);
        $this->getEntityManager()->flush();
    }

    public function findAllEventsForUser(User $user)
    {
        $qb = $this->createQueryBuilder('tce');
//        $qb->select('u1.id as debtor')->addSelect('u2.id as loaner');
        $qb->leftJoin(Transaction::class, 't', 'ON', 'tce.transaction = t.id')
            ->leftJoin(Debt::class, 'd', 'ON', 'd.transaction = t.id')
            ->leftJoin(Loan::class, 'l', 'ON', 'l.transaction = t.id')
            ->leftJoin(User::class, 'u1', 'ON', 'd.owner = u1.id')
            ->leftJoin(User::class, 'u2', 'ON', 'l.owner = u2.id')
            ->where('u1.id = :user')
            ->orWhere('u2.id = :user')
            ->setParameter('user', $user->getId())
            ->distinct();
        return $qb->getQuery()->getResult();
    }


    /*
    public function findOneBySomeField($value): ?TransactionStateChangeEvent
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
