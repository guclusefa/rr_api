<?php

namespace App\Repository;

use App\Entity\User;
use App\Service\PaginatorService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
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

    public function isAccesibleToMe($user, $me): bool
    {
        // IF BANNED AND NOT ME RETURN FALSE
        if ($user->isIsBanned() && $user->getId() !== $me->getId()) {
            return false;
        }

        return true;
    }

    public function findByBanned($qb, $user)
    {
        if ($user){
            // find all the users that isBanned = 0 unless the user is the current user
            $qb->andWhere('u.isBanned = :isBanned')
                ->andWhere('u.id != :id')
                ->setParameter('isBanned', 0)
                ->setParameter('id', $user->getId());
        } else {
            // find all the users that isBanned = 0
            $qb->andWhere('u.isBanned = :isBanned')
                ->setParameter('isBanned', 0);
        }
    }

    public function findBySearch($qb, $search)
    {
        if ($search) {
            $qb->andWhere('u.username LIKE :search')
                ->orWhere('u.firstName LIKE :search')
                ->orWhere('u.lastName LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }
    }

    public function findByCertified($qb, $certified)
    {
        if ($certified) {
            $qb->andWhere('u.isVerified = :certified')
                ->setParameter('certified', $certified);
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

    public function advanceSearch($user, $search, $certified, $states, $genders, $order, $direction, $page, $limit): array
    {
        $qb = $this->createQueryBuilder('u');
        $this->findByBanned($qb, $user);
        $this->findBySearch($qb, $search);
        $this->findByCertified($qb, $certified);
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

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
