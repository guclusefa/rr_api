<?php

namespace App\Repository;

use App\Entity\ResourceSharedTo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ResourceSharedTo>
 *
 * @method ResourceSharedTo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResourceSharedTo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResourceSharedTo[]    findAll()
 * @method ResourceSharedTo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceSharedToRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResourceSharedTo::class);
    }

    public function save(ResourceSharedTo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ResourceSharedTo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ResourceSharedTo[] Returns an array of ResourceSharedTo objects
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

//    public function findOneBySomeField($value): ?ResourceSharedTo
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
