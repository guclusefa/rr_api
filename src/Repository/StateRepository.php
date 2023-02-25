<?php

namespace App\Repository;

use App\Entity\State;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<State>
 *
 * @method State|null find($id, $lockMode = null, $lockVersion = null)
 * @method State|null findOneBy(array $criteria, array $orderBy = null)
 * @method State[]    findAll()
 * @method State[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StateRepository extends ServiceEntityRepository
{
    public function __construct
    (
        ManagerRegistry $registry,
        private readonly PaginatorService $paginatorService
    )
    {
        parent::__construct($registry, State::class);
    }

    public function save(State $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(State $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return State[] Returns an array of State objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?State
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findBySearch($qb, $search)
    {
        if ($search) {
            // trime the spaces at the beginning and the end of the string
            $search = trim($search);
            $qb->andWhere('s.name LIKE :search')
                ->orWhere('s.code LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
    }

    public function orderBy($qb, $order, $direction)
    {
        $qb->orderBy('s.' . $order, $direction);
    }

    public function advanceSearch($search, $order, $direction, $page, $limit): array
    {
        $qb = $this->createQueryBuilder('s');

        $this->findBySearch($qb, $search);

        $this->orderBy($qb, $order, $direction);
        $paginator = $this->paginatorService->paginate($qb, $page, $limit);
        $metadata = $this->paginatorService->getMetadata($paginator, $page, $limit);

        return [
            'data' => $qb->getQuery()->getResult(),
            'meta' => $metadata,
        ];
    }
}
