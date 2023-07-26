<?php

namespace App\Service\GroupEvent\UserCollection;

use App\Entity\GroupEventUserCollection;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroupEventUserCollection>
 *
 * @method GroupEventUserCollection|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupEventUserCollection|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupEventUserCollection[]    findAll()
 * @method GroupEventUserCollection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupEventUserCollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupEventUserCollection::class);
    }

    public function save(GroupEventUserCollection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GroupEventUserCollection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return GroupEventUserCollection[] Returns an array of GroupEventUserCollection objects
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

//    public function findOneBySomeField($value): ?GroupEventUserCollection
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function persist(GroupEventUserCollection $groupEventUserCollection): void
    {
        $this->_em->persist($groupEventUserCollection);
        $this->_em->flush();
    }

    public function getGroupByUserList(array $users)
    {
        $userIds = array_map(function (User $user) {
            return $user->getId();
        }, $users);

        $queryBuilder = $this->createQueryBuilder('uc');

        $queryBuilder->select('uc')
            ->leftJoin('uc.users', 'u', $queryBuilder->expr()->in('u.id', $userIds))
//            ->where($queryBuilder->expr()->in('u.id', $userIds))
            ->groupBy('uc.id')
            ->having(
                $queryBuilder->expr()->eq(
                    $queryBuilder->expr()->count('uc.id'),
                    count($userIds)
                )
            )
        ;

        $result = $queryBuilder->getQuery()->getOneOrNullResult();
        return $result;
    }
}
