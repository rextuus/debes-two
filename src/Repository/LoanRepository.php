<?php

namespace App\Repository;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Loan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Loan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Loan[]    findAll()
 * @method Loan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    /**
     * persist
     *
     * @param TransactionPartInterface $loan
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist(TransactionPartInterface $loan): void
    {
        $this->_em->persist($loan);
        $this->_em->flush();
    }

    /**
     * findTransactionsForUser
     * @param User $owner
     *
     * @return int|mixed|string
     */
    public function findTransactionsForUser(User $owner, array $filter)
    {
        $qb = $this->createQueryBuilder('l')
            ->select('t')
            ->leftJoin(Transaction::class, 't', 'WITH', 'l.transaction = t.id')
            ->where('l.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('l.amount', 'ASC');

        if (array_key_exists('limit', $filter)){
            $qb->setMaxResults($filter['limit']);
        }
        if (array_key_exists('states', $filter)){
            $qb->andWhere('l.state IN (:states)');
            $qb->setParameter('states', $filter['states']);
        }
        if (array_key_exists('order', $filter)) {
            $qb->orderBy('l.edited', $filter['order']);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * getTotalLoansForUser
     *
     * @param User $owner
     *
     * @return float
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalLoansForUser(User $owner, array $states = [Transaction::STATE_ACCEPTED, Transaction::STATE_READY]): float
    {
        $qb = $this->createQueryBuilder('l')
            ->select('SUM(l.amount)')
            ->where('l.owner = :owner')
            ->andWhere('l.state IN (:states)')
            ->setParameter('owner', $owner)
            ->setParameter('states', $states);

        return (float)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * getAllDebtTransactionsForUserAndSate
     *
     * @param User $owner
     * @param string $state
     * @param float $amount
     *
     * @return int|mixed|string
     */
    public function getAllLoanTransactionsForUserAndSate(User $owner, string $state, float $amount)
    {
        $qb = $this->createQueryBuilder('l')
            ->select('l')
            ->leftJoin(Transaction::class, 't', 'WITH', 'l.transaction = t.id')
            ->where('l.owner = :owner')
            ->andWhere('l.state = :state')
            ->andWhere('l.amount >= :amount')
            ->setParameter('owner', $owner)
            ->setParameter('state', $state)
            ->setParameter('amount', $amount)
            ->orderBy('t.created', 'ASC');
        return $qb->getQuery()->getResult();
    }

    public function getCountForAllLoanTransactionsForUserAndSate(User $owner, string $state, float $amount): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('count(l)')
            ->leftJoin(Transaction::class, 't', 'WITH', 'l.transaction = t.id')
            ->where('l.owner = :owner')
            ->andWhere('l.state = :state')
            ->andWhere('l.amount >= :amount')
            ->setParameter('owner', $owner)
            ->setParameter('state', $state)
            ->setParameter('amount', $amount)
            ->orderBy('t.created', 'ASC');
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * getAllExchangeLoansForDebt
     *
     * @param User $debtor
     * @param string $state
     * @param float $amount
     * @param array $loaner
     *
     * @return int|mixed|string
     */
    public function getAllExchangeLoansForDebt(
        User   $debtor,
        string $state,
        float  $amount,
        array  $loaner
    )
    {
        return $this->createQueryBuilder('l')
            ->select('l')
            ->innerJoin(Transaction::class, 't', 'WITH', 'l.transaction = t.id')
            ->innerJoin(Debt::class, 'd', 'WITH', 'd.transaction = t.id')
            ->innerJoin(User::class, 'u', 'WITH', 'd.owner = u.id')
            ->where('l.owner = :debtor')
            ->andWhere('t.state = :state')
            ->andWhere('l.amount >= :amount')
            ->andWhere('u.id IN (:loaner)')
            ->setParameter('debtor', $debtor)
            ->setParameter('state', $state)
            ->setParameter('amount', $amount)
            ->setParameter('loaner', $loaner)
            ->orderBy('t.created', 'ASC')
            ->getQuery()->getResult();
    }
}
