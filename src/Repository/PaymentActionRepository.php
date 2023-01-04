<?php

namespace App\Repository;

use App\Entity\BankAccount;
use App\Entity\PaymentAction;
use App\Entity\PaymentOption;
use App\Entity\PaypalAccount;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentAction[]    findAll()
 * @method PaymentAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentAction::class);
    }

    // /**
    //  * @return PaymentAction[] Returns an array of PaymentAction objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PaymentAction
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function persist(PaymentAction $paymentAction): void
    {
        $this->_em->persist($paymentAction);
        $this->_em->flush();
    }

    /**
     * @return PaymentAction[]
     */
    public function findAllBelongingToProvider(User $user): array
    {
        //            ->innerJoin(Transaction::class, 't', 'WITH', 'l.transaction = t.id')
        $qb = $this->createQueryBuilder('p');
        $qb->leftJoin(BankAccount::class, 'b', 'WITH', 'p.bankAccountSender = b.id')
            ->leftJoin(PaypalAccount::class, 'pa', 'WITH', 'p.paypalAccountSender = pa.id')
            ->leftJoin(User::class, 'u', 'WITH', 'b.owner = u.id')
            ->leftJoin(User::class, 'u2', 'WITH', 'pa.owner = u2.id')
            ->where('u.id = :user')
            ->orWhere('u2.id = :user')
            ->setParameter('user', $user->getId());
        return ($qb->getQuery()->getResult());
    }
}
