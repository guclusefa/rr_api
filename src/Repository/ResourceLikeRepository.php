<?php

namespace App\Repository;

use App\Entity\ResourceLike;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceLike>
 *
 * @method ResourceLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceLike[]    findAll()
 * @method ResourceLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceLikeRepository extends ServiceEntityRepository
{
    public function __construct
    (
        ManagerRegistry $registry,
        private readonly PaginatorService $paginatorService,
    )
    {
        parent::__construct($registry, ResourceLike::class);
    }

    public function save(ResourceLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ResourceLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function advanceSearch($resource, $page, $limit
    ): array
    {
        $qb = $this->createQueryBuilder('rL');
        if ($resource) {
            $qb->andWhere('rL.resource = :resource')
                ->setParameter('resource', $resource);
        }

        $paginator = $this->paginatorService->paginate($qb, $page, $limit);
        $metadata = $this->paginatorService->getMetadata($paginator, $page, $limit);

        return [
            'data' => $qb->getQuery()->getResult(),
            'meta' => $metadata,
        ];
    }


//    /**
//     * @return ResourceLike[] Returns an array of ResourceLike objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ResourceLike
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
