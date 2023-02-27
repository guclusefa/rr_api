<?php

namespace App\Repository;

use App\Entity\Relation;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Relation>
 *
 * @method Relation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Relation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Relation[]    findAll()
 * @method Relation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelationRepository extends ServiceEntityRepository
{
    public function __construct
    (
        ManagerRegistry $registry,
        private readonly PaginatorService $paginatorService
    )
    {
        parent::__construct($registry, Relation::class);
    }

    public function save(Relation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Relation $entity, bool $flush = false): void
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
            $qb->andWhere('re.name LIKE :search')
                ->orWhere('re.code LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
    }

    public function orderBy($qb, $order, $direction)
    {
        $qb->orderBy('re.' . $order, $direction);
    }

    public function advanceSearch($search, $order, $direction, $page, $limit): array
    {
        $qb = $this->createQueryBuilder('re');

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
//     * @return Relation[] Returns an array of Relation objects
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

//    public function findOneBySomeField($value): ?Relation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
