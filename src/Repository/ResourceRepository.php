<?php

namespace App\Repository;

use App\Entity\Resource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resource>
 *
 * @method Resource|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resource|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resource[]    findAll()
 * @method Resource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resource::class);
    }

    public function save(Resource $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Resource $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findBySearch($qb, $search)
    {
        if ($search) {
            $qb->andWhere('r.title LIKE :search')
                ->orWhere('r.content LIKE :search')
                ->orWhere('r.link LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }
    }

    public function findByAuthors($qb, $authors)
    {
        if ($authors) {
            $qb->andWhere('r.author IN (:authors)')
                ->setParameter('authors', $authors);
        }
    }

    public function findByRelations($qb, $relations)
    {
        if ($relations) {
            $qb->andWhere('r.relation IN (:relations)')
                ->setParameter('relations', $relations);
        }
    }

    public function findByCategories($qb, $categories)
    {
        if ($categories) {
            $qb->join('r.categories', 'c')
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $categories);
        }
    }

    public function orderBy($qb, $order, $direction)
    {
        if ($order && $direction) {
            $qb->orderBy('r.'.$order, $direction);
        }
    }

    public function paginate($qb, $page, $limit): Paginator
    {
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    public function getMetadata($paginator, $page, $limit): array
    {
        return [
            'page' => (int) $page,
            'limit' => (int) $limit,
            'pages' => (int) ceil($paginator->count() / $limit),
            'total' => $paginator->count(),
            'start' => ($page - 1) * $limit + 1,
            'end' => $page * $limit,
        ];
    }

    public function advanceSearch($search, $authors, $relations, $categories, $order, $direction, $page, $limit): array
    {
        $qb = $this->createQueryBuilder('r');
        $this->findBySearch($qb, $search);
        $this->findByAuthors($qb, $authors);
        $this->findByRelations($qb, $relations);
        $this->findByCategories($qb, $categories);
        $this->orderBy($qb, $order, $direction);
        $paginator = $this->paginate($qb, $page, $limit);
        $metadata = $this->getMetadata($paginator, $page, $limit);
        return [
            'data' => $qb->getQuery()->getResult(),
            'meta' => $metadata,
        ];
    }

//    /**
//     * @return Resource[] Returns an array of Resource objects
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

//    public function findOneBySomeField($value): ?Resource
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
