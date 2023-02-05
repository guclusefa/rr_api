<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserBan;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorService $paginatorService
    )
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :identifier')
            ->orWhere('u.username = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function isBanned(User $user): bool
    {
        $bans = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('b')
            ->from(UserBan::class, 'b')
            ->where('b.user = :user')
            ->andWhere('b.endDate IS NULL OR b.endDate > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        return count($bans) > 0;
    }

    public function getMostCurrentBan(User $user): ?UserBan
    {
        $bans = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('b')
            ->from(UserBan::class, 'b')
            ->where('b.user = :user')
            ->andWhere('b.endDate IS NULL OR b.endDate > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return count($bans) > 0 ? $bans[0] : null;
    }

    public function isAccesibleToMe($user): bool
    {
        // IF BANNED
        if ($this->isBanned($user)) {
            return false;
        }
        return true;
    }

    public function findBannedUsers($qb)
    {
        // If user.bans is not empty and if one of the UserBan.endDate is null or in the future then the user is banned
        $qb->join(UserBan::class, 'ub', Join::WITH, 'ub.user = u')
            ->where($qb->expr()->orX(
                $qb->expr()->isNull('ub.endDate'),
                $qb->expr()->gte('ub.endDate', ':now')
            ))
            ->setParameter('now', new \DateTime());
    }

    public function findNonBannedUsers($qb)
    {
        // If user.bans is empty then the user is not banned
        // IF UserBan.endDate is not null or UserBan.endDate is in the past then the user is not banned
        $qb->where('NOT EXISTS (
                SELECT b FROM App\Entity\UserBan b 
                WHERE b.user = u 
                AND (b.endDate IS NULL OR b.endDate >= :now)
            )')
            ->setParameter('now', new \DateTime());
    }

    public function findBySearch($qb, $search)
    {
        if ($search) {
            // trime the spaces at the beginning and the end of the string
            $search = trim($search);
            $qb->andWhere('u.username LIKE :search')
                ->orWhere('u.firstName LIKE :search')
                ->orWhere('u.lastName LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }
    }

    public function findByCertified($qb, $certified)
    {
        if ($certified) {
            $qb->andWhere('u.isCertified = :certified')
                ->setParameter('certified', $certified);
        }
    }

    public function findByRoles($qb, $roles)
    {
        if ($roles) {
            // roles is  something like ['ROLE_USER', 'ROLE_ADMIN']
            // u.roles is something like ['ROLE_USER']
            // u.roles is a json array
            for ($i = 0; $i < count($roles); $i++) {
                if ($i == 0) {
                    $qb->andWhere('u.roles LIKE :role'.$i);
                } else {
                    $qb->orWhere('u.roles LIKE :role'.$i);
                }
                $qb->setParameter('role'.$i, '%'.$roles[$i].'%');
            }
        }
    }


    public function findByStates($qb, $states)
    {
        if ($states) {
            $qb->andWhere('u.state IN (:states)')
                ->setParameter('states', $states);
        }
    }

    public function findByGenders($qb, $genders)
    {
        if ($genders) {
            $qb->andWhere('u.gender IN (:genders)')
                ->setParameter('genders', $genders);
        }
    }

    public function orderBy($qb, $order, $direction)
    {
        if ($order && $direction) {
            $qb->orderBy('u.'.$order, $direction);
        }
    }

    public function advanceSearch($search, $certified, $roles, $states, $genders, $order, $direction, $page, $limit): array
    {
        $qb = $this->createQueryBuilder('u');

        $this->findNonBannedUsers($qb);

        $this->findBySearch($qb, $search);
        $this->findByCertified($qb, $certified);

        $this->findByRoles($qb, $roles);
        $this->findByStates($qb, $states);
        $this->findByGenders($qb, $genders);

        $this->orderBy($qb, $order, $direction);
        $paginator = $this->paginatorService->paginate($qb, $page, $limit);
        $metadata = $this->paginatorService->getMetadata($paginator, $page, $limit);

        return [
            'data' => $qb->getQuery()->getResult(),
            'meta' => $metadata,
        ];
    }
}
