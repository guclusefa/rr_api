<?php

namespace App\Repository;

use App\Entity\ResourceShare;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceShare>
 *
 * @method ResourceShare|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceShare|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceShare[]    findAll()
 * @method ResourceShare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceShareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly PaginatorService $paginatorService)
    {
        parent::__construct($registry, ResourceShare::class);
    }

    public function save(ResourceShare $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ResourceShare $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function advanceSearch($resource, $page, $limit
    ): array
    {
        $qb = $this->createQueryBuilder('rSH');
        if ($resource) {
            $qb->andWhere('rSH.resource = :resource')
                ->setParameter('resource', $resource);
        }

        // order by createdAt most recent first
        $qb->orderBy('rSH.createdAt', 'DESC');

        $paginator = $this->paginatorService->paginate($qb, $page, $limit);
        $metadata = $this->paginatorService->getMetadata($paginator, $page, $limit);

        return [
            'data' => $qb->getQuery()->getResult(),
            'meta' => $metadata,
        ];
    }

//    /**
//     * @return ResourceShare[] Returns an array of ResourceShare objects
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

//    public function findOneBySomeField($value): ?ResourceShare
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
