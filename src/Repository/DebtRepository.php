<?php

namespace App\Repository;

use App\Entity\Debt;
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
 * @method Debt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Debt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Debt[]    findAll()
 * @method Debt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Debt::class);
    }

    /**
     * persist
     *
     * @param Debt $debt
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist(TransactionPartInterface $debt): void
    {
        $this->_em->persist($debt);
        $this->_em->flush();
    }

    /**
     * findTransactionsForUser
     *
     * @param User $owner
     *
     * @return int|mixed|string
     */
    public function findTransactionsForUser(User $owner, array $filter): mixed
    {
        $qb = $this->createQueryBuilder('d')
            ->select('t')
            ->leftJoin(Transaction::class, 't', 'WITH', 'd.transaction = t.id')
            ->where('d.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('d.amount', 'ASC');

        if (array_key_exists('limit', $filter)) {
            $qb->setMaxResults($filter['limit']);
        }
        if (array_key_exists('states', $filter)) {
            $qb->andWhere('d.state IN (:states)');
            $qb->setParameter('states', $filter['states']);
        }
        if (array_key_exists('order', $filter)) {
            $qb->orderBy('d.edited', $filter['order']);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * getTotalDebtsForUser
     *
     * @param User $owner
     *
     * @return float
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalDebtsForUser(User $owner, array $states = [Transaction::STATE_ACCEPTED, Transaction::STATE_READY]): float
    {
        $qb = $this->createQueryBuilder('d')
            ->select('SUM(d.amount)')
            ->where('d.owner = :owner')
            ->andWhere('d.state IN (:states)')
            ->setParameter('owner', $owner)
            ->setParameter('states', $states);

        return (float)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * findTransactionsForUserAndState
     * @param User $owner
     * @param string $state
     *
     * @return int|mixed|string
     */
    public function findAllDebtsForUserAndState(User $owner, string $state)
    {
        return $this->createQueryBuilder('d')
            ->select('d')
            ->where('d.owner = :owner')
            ->andWhere('d.state = :state')
            ->setParameter('owner', $owner)
            ->setParameter('state', $state)
            ->orderBy('d.created', 'ASC')
            ->getQuery()->getResult();
    }

    public function getCountForAllDebtsForUserAndState(User $owner, string $state)
    {
        return $this->createQueryBuilder('d')
            ->select('count(d)')
            ->where('d.owner = :owner')
            ->andWhere('d.state = :state')
            ->setParameter('owner', $owner)
            ->setParameter('state', $state)
            ->orderBy('d.created', 'ASC')
            ->getQuery()->getSingleScalarResult();
    }
}
