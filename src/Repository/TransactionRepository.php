<?php

namespace App\Repository;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * persist
     *
     * @param Transaction $transaction
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist(Transaction $transaction): void
    {
        $this->_em->persist($transaction);
        $this->_em->flush();
    }

    public function getTransactionCountBetweenUsers(User $debtor, User $loaner) {
        $qb=  $this->createQueryBuilder('t');
        $qb->select('count (t) as count')
            ->leftJoin(Debt::class, 'd', 'WITH', 'd.transaction = t.id')
            ->leftJoin(Loan::class, 'l', 'WITH', 'l.transaction = t.id')
            ->where('d.owner = :debtor')
            ->andWhere('l.owner = :loaner')
            ->setParameter('debtor', $debtor)
            ->setParameter('loaner', $loaner);
        return $qb->getQuery()->getSingleScalarResult();
    }

    // /**
    //  * @return Transaction[] Returns an array of Transaction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Transaction
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getTotalDebtsBetweenUsers(User $debtor, User $loaner)
    {
        $qb=  $this->createQueryBuilder('t');
        $qb->select('sum(d.amount) as total')
            ->leftJoin(Debt::class, 'd', 'WITH', 'd.transaction = t.id')
            ->leftJoin(Loan::class, 'l', 'WITH', 'l.transaction = t.id')
            ->where('d.owner = :debtor')
            ->andWhere('l.owner = :loaner')
            ->setParameter('debtor', $debtor)
            ->setParameter('loaner', $loaner);
        return $qb->getQuery()->getSingleScalarResult();
    }
}
