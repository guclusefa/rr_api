<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        // create 1 user
        $user = new User();
        $user->setEmail("user@gmail.com");
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        // create 1 admin
        $admin = new User();
        $admin->setEmail("admin@gmail.com");
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // flush
        $manager->flush();
    }
}
