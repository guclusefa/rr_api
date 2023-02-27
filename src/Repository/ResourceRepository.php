<?php

namespace App\Repository;

use App\Entity\Resource;
use App\Entity\ResourceSharedTo;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorService $paginatorService,
        private readonly UserRepository $userRepository
    )
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

    public function isAccesibleToMe($resource, $user): bool
    {
        // if author is banned
        if ($this->userRepository->isBanned($resource->getAuthor())) {
            return false;
        }
        // public
        if ($resource->getVisibility() == 1) {
            return true;
        }
        // shared
        if ($resource->getVisibility() == 2) {
            if ($user) {
                // sharedTo me
                $sharedToResponsitory = $this->getEntityManager()->getRepository(ResourceSharedTo::class);
                $sharedToMe = $sharedToResponsitory->findOneBy(['resource' => $resource, 'user' => $user]);
                if ($sharedToMe) {
                    return true;
                }
            }
        }
        // private
        if ($resource->getVisibility() == 3) {
            if ($user) {
                // author me
                if ($resource->getAuthor() == $user) {
                    return true;
                }
            }
        }

        return false;
    }

    // hot fix a revoir TODO
    public function findByNonBannedAuthors($qb)
    {
        $qb->join('r.author', 'a')
            ->leftJoin('a.bans', 'ub', Join::WITH,
                $qb->expr()->orX(
                    $qb->expr()->isNull('ub.endDate'),
                    $qb->expr()->gt('ub.endDate', ':now')
                )
            )
            ->groupBy('r')
            ->having($qb->expr()->eq('COUNT(ub)', ':count'))
            ->setParameter('now', new \DateTime())
            ->setParameter('count', 0);
    }

    public function findByAccesibility($qb, $user)
    {
        // FIND all with visibility 1
        // OR FIND all with visibility 2 & sharedTo me
        // OR FIND all with visibility 3 & author me
        $qb->andWhere('r.visibility = 1')
            ->orWhere('r.visibility = 2 AND EXISTS (
                SELECT rst.id
                FROM App\Entity\ResourceSharedTo rst
                WHERE rst.resource = r.id AND rst.user = :user
            )')
            ->orWhere('r.visibility = 3 AND r.author = :user')
            ->setParameter('user', $user);
    }

    public function findByStatus($qb, $user)
    {
        // FIND all with author me
        // OR FIND all with isPublished 1 & isSuspended 0
        $qb->andWhere('r.author = :user')
            ->orWhere('r.isPublished = 1 AND r.isSuspended = 0')
            ->setParameter('user', $user);
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

    public function findByVerified($qb, $verified)
    {
        if ($verified) {
            $qb->andWhere('r.isVerified = :verified')
                ->setParameter('verified', $verified);
        }
    }

    public function findByVisibility($qb, $visibility)
    {
        if ($visibility) {
            $qb->andWhere('r.visibility = :visibility')
                ->setParameter('visibility', $visibility);
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

    public function orderByComments($qb, $direction)
    {
        if ($direction) {
            $qb->addSelect('COUNT(rc.id) AS HIDDEN comments')
                ->leftJoin('r.comments', 'rc')
                ->groupBy('r.id')
                ->orderBy('comments', $direction);
        }
    }

    public function orderByLikes($qb, $direction)
    {
        if ($direction) {
            $qb->addSelect('COUNT(rl.id) AS HIDDEN likes')
                ->leftJoin('r.likes', 'rl')
                ->groupBy('r.id')
                ->orderBy('likes', $direction);
        }
    }

    public function orderByShares($qb, $direction)
    {
        if ($direction) {
            $qb->addSelect('COUNT(rs.id) AS HIDDEN shares')
                ->leftJoin('r.shares', 'rs')
                ->groupBy('r.id')
                ->orderBy('shares', $direction);
        }
    }

    public function orderByExploits($qb, $direction)
    {
        if ($direction) {
            $qb->addSelect('COUNT(re.id) AS HIDDEN exploits')
                ->leftJoin('r.exploits', 're')
                ->groupBy('r.id')
                ->orderBy('exploits', $direction);
        }
    }

    public function orderBySaves($qb, $direction)
    {
        if ($direction) {
            $qb->addSelect('COUNT(rs.id) AS HIDDEN saves')
                ->leftJoin('r.saves', 'rs')
                ->groupBy('r.id')
                ->orderBy('saves', $direction);
        }
    }

    public function orderByConsults($qb, $direction)
    {
        if ($direction) {
            $qb->addSelect('COUNT(rs.id) AS HIDDEN saves')
                ->leftJoin('r.saves', 'rs')
                ->groupBy('r.id')
                ->orderBy('saves', $direction);
        }
    }

    public function orderBy($qb, $order, $direction)
    {
        if ($order && $direction) {
            switch ($order) {
                case 'likes':
                    $this->orderByLikes($qb, $direction);
                    break;
                case 'comments':
                    $this->orderByComments($qb, $direction);
                    break;
                case 'shares':
                    $this->orderByShares($qb, $direction);
                    break;
                case 'exploits':
                    $this->orderByExploits($qb, $direction);
                    break;
                case 'saves':
                    $this->orderBySaves($qb, $direction);
                    break;
                case 'consults':
                    $this->orderByConsults($qb, $direction);
                    break;
                default:
                    $qb->orderBy('r.'.$order, $direction);
                    break;
            }
        }
    }

    public function advanceSearch($user, $search, $verified, $visibility, $authors, $relations, $categories, $order, $direction, $page, $limit): array
    {
        $qb = $this->createQueryBuilder('r');

        $this->findByNonBannedAuthors($qb);

        $this->findByAccesibility($qb, $user);
        $this->findByStatus($qb, $user);

        $this->findBySearch($qb, $search);
        $this->findByVerified($qb, $verified);
        $this->findByVisibility($qb, $visibility);
        $this->findByAuthors($qb, $authors);
        $this->findByRelations($qb, $relations);
        $this->findByCategories($qb, $categories);

        $this->orderBy($qb, $order, $direction);

        $paginator = $this->paginatorService->paginate($qb, $page, $limit);
        $metadata = $this->paginatorService->getMetadata($paginator, $page, $limit);

        return [
            'data' => $qb->getQuery()->getResult(),
            'meta' => $metadata,
        ];
    }
}
