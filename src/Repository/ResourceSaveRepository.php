<?php

namespace App\Repository;

use App\Entity\ResourceSave;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceSave>
 *
 * @method ResourceSave|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceSave|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceSave[]    findAll()
 * @method ResourceSave[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceSaveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly PaginatorService $paginatorService)
    {
        parent::__construct($registry, ResourceSave::class);
    }

    public function save(ResourceSave $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ResourceSave $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function advanceSearch($resource, $page, $limit
    ): array
    {
        $qb = $this->createQueryBuilder('rS');
        if ($resource) {
            $qb->andWhere('rS.resource = :resource')
                ->setParameter('resource', $resource);
        }

        // order by createdAt most recent first
        $qb->orderBy('rS.createdAt', 'DESC');

        $paginator = $this->paginatorService->paginate($qb, $page, $limit);
        $metadata = $this->paginatorService->getMetadata($paginator, $page, $limit);

        return [
            'data' => $qb->getQuery()->getResult(),
            'meta' => $metadata,
        ];
    }

//    /**
//     * @return ResourceSave[] Returns an array of ResourceSave objects
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

//    public function findOneBySomeField($value): ?ResourceSave
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
