<?php

namespace App\Repository;

use App\Entity\Debt;
use App\Entity\Exchange;
use App\Entity\Loan;
use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Exchange|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exchange|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exchange[]    findAll()
 * @method Exchange[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExchangeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exchange::class);
    }

    /**
     * persist
     *
     * @param Exchange $exchange
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist(Exchange $exchange): void
    {
        $this->_em->persist($exchange);
        $this->_em->flush();
    }

    public function findCorrespondingExchange(Transaction $transaction, Debt $debt, Loan $loan)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('t')
            ->leftJoin(Transaction::class, 't', 'WITH', 'e.transaction = t.id')
            ->where('e.debt = :debt')
            ->andWhere('e.loan = :loan')
            ->andWhere('e.transaction != :transaction')
            ->setParameter('debt', $debt)
            ->setParameter('loan', $loan)
            ->setParameter('transaction', $transaction);

            return $qb->getQuery()->getSingleResult();
    }

    // /**
    //  * @return Exchange[] Returns an array of Exchange objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Exchange
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

}
