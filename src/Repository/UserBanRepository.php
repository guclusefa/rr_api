<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserBan;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserBan>
 *
 * @method UserBan|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBan|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBan[]    findAll()
 * @method UserBan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBanRepository extends ServiceEntityRepository
{
    public function __construct
    (
        ManagerRegistry $registry,
        private readonly PaginatorService $paginatorService
    )
    {
        parent::__construct($registry, UserBan::class);
    }

    public function save(UserBan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserBan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBySearch($qb, $search)
    {
        if ($search) {
            // trime the spaces at the beginning and the end of the string
            $search = trim($search);
            $qb->andWhere('ub.reason LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
    }

    public function orderBy($qb, $order, $direction)
    {
        $qb->orderBy('ub.' . $order, $direction);
    }

    public function advanceSearch($search, $order, $direction, $page, $limit): array
    {
        $qb = $this->createQueryBuilder('ub');

        $this->findBySearch($qb, $search);

        $this->orderBy($qb, $order, $direction);
        $paginator = $this->paginatorService->paginate($qb, $page, $limit);
        $metadata = $this->paginatorService->getMetadata($paginator, $page, $limit);

        return [
            'data' => $qb->getQuery()->getResult(),
            'meta' => $metadata,
        ];
    }

//    /**
//     * @return UserBan[] Returns an array of UserBan objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserBan
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
