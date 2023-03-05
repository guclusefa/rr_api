<?php

namespace App\Repository;

use App\Entity\ResourceConsult;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceConsult>
 *
 * @method ResourceConsult|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceConsult|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceConsult[]    findAll()
 * @method ResourceConsult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceConsultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly PaginatorService $paginatorService)
    {
        parent::__construct($registry, ResourceConsult::class);
    }

    public function save(ResourceConsult $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ResourceConsult $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function advanceSearch($resource, $page, $limit
    ): array
    {
        $qb = $this->createQueryBuilder('rC');
        if ($resource) {
            $qb->andWhere('rC.resource = :resource')
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
//     * @return ResourceConsult[] Returns an array of ResourceConsult objects
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

//    public function findOneBySomeField($value): ?ResourceConsult
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
