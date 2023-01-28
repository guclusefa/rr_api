<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorService $paginatorService
    )
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByNonBannedAuthors($qb)
    {
        $subquery = $qb->getEntityManager()->createQueryBuilder()
            ->select('COUNT(ub.id)')
            ->from('App\Entity\UserBan', 'ub')
            ->where('ub.user = a')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('ub.endDate'),
                    $qb->expr()->gt('ub.endDate', ':now')
                )
            )
            ->getDQL();

        $qb->join('c.author', 'a')
            ->having($qb->expr()->eq(0, "($subquery)"))
            ->setParameter('now', new \DateTime());
    }

    public function findByAccesibility($qb, $user)
    {
        // FIND all with resource visibility 1
        // OR FIND all with resource visibility 2 & sharedTo me
        // OR FIND all with resource visibility 3 & author me
        $qb->join('c.resource', 'r')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('r.visibility', 1),
                    $qb->expr()->andX(
                        $qb->expr()->eq('r.visibility', 2),
                        $qb->expr()->in('r.id', ':sharedToMe')
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->eq('r.visibility', 3),
                        $qb->expr()->eq('r.author', ':author')
                    )
                )
            )
            ->setParameter('sharedToMe', $user->getSharesTo())
            ->setParameter('author', $user);
    }

    public function findBySearch($qb, $search)
    {
        if ($search) {
            $qb->andWhere('c.content LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }
    }

    public function findByAuthors($qb, $authors)
    {
        if ($authors) {
            $qb->andWhere('c.author IN (:authors)')
                ->setParameter('authors', $authors);
        }
    }

    public function findByResources($qb, $resources)
    {
        if ($resources) {
            $qb->andWhere('c.resource IN (:resources)')
                ->setParameter('resources', $resources);
        }
    }

    public function findByReplyTo($qb, $replyTo)
    {
        if ($replyTo) {
            $qb->andWhere('c.replyTo IN (:replyTo)')
                ->setParameter('replyTo', $replyTo);
        }
    }

    public function orderBy($qb, $order, $direction)
    {
        if ($order && $direction) {
            $qb->orderBy('c.'.$order, $direction);
        }
    }

    public function advanceSearch($user, $seach, $authors, $resources, $replyTo, $order, $direction, $page, $limit): array
    {
        $qb = $this->createQueryBuilder('c');

        $this->findByNonBannedAuthors($qb);
        $this->findByAccesibility($qb, $user);

        $this->findBySearch($qb, $seach);
        $this->findByAuthors($qb, $authors);
        $this->findByResources($qb, $resources);
        $this->findByReplyTo($qb, $replyTo);

        $this->orderBy($qb, $order, $direction);
        $paginator = $this->paginatorService->paginate($qb, $page, $limit);
        $metadata = $this->paginatorService->getMetadata($paginator, $page, $limit);
        return [
            'data' => $paginator->getQuery()->getResult(),
            'meta' => $metadata,
        ];
    }
}
