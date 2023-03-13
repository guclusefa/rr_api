<?php

namespace App\Fixtures;

use App\Entity\User;
use App\Repository\StateRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements OrderedFixtureInterface
{
    public function __construct
    (
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly StateRepository $stateRepository
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        // state
        $states = $this->stateRepository->findAll();
        $genders = ["M", "F", "O"];

        // create 1 super admin
        $admin = new User();
        $admin->setEmail("superadmin@gmail.com");
        $admin->setUsername("superadmin");
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'));
        $admin->setFirstName("Sefa");
        $admin->setLastName("GUCLU");
        $admin->setGender("M");
        $admin->setBio("Biographie super admin");
        $admin->setBirthDate(new \DateTime("2002-11-15"));
        $admin->setRoles(['ROLE_SUPER_ADMIN']);
        $admin->setState($states[array_rand($states)]);
        $admin->setIsVerified(true);
        $admin->setIsCertified(true);
        $manager->persist($admin);

        // create 1 admin
        $admin = new User();
        $admin->setEmail("admin@gmail.com");
        $admin->setUsername("admin");
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'));
        $admin->setFirstName("Sefa");
        $admin->setLastName("GUCLU");
        $admin->setGender("M");
        $admin->setBio("Biographie admin");
        $admin->setBirthDate(new \DateTime("2002-11-15"));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setState($states[array_rand($states)]);
        $admin->setIsVerified(true);
        $admin->setIsCertified(true);
        $manager->persist($admin);

        // create 1 moderator
        $moderator = new User();
        $moderator->setEmail("moderator@gmail.com");
        $moderator->setUsername("moderator");
        $moderator->setPassword($this->userPasswordHasher->hashPassword($moderator, 'password'));
        $moderator->setFirstName("Sefa");
        $moderator->setLastName("GUCLU");
        $moderator->setGender("M");
        $moderator->setBio("Biographie moderator");
        $moderator->setBirthDate(new \DateTime("2002-11-15"));
        $moderator->setRoles(['ROLE_MODERATOR']);
        $moderator->setState($states[array_rand($states)]);
        $moderator->setIsVerified(true);
        $moderator->setIsCertified(true);
        $manager->persist($moderator);

        // create 100 random users
        $faker = \Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setUsername($faker->userName);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setGender($genders[array_rand($genders)]);
            $user->setBio("bio ${i}");
            $user->setBirthDate($faker->dateTimeBetween('-50 years', '-18 years'));
            $user->setState($states[array_rand($states)]);
            $user->setRoles(['ROLE_USER']);
            $isVerified = rand(0, 1);
            $user->setIsVerified((bool)$isVerified);
            $isCertified = rand(0, 1);
            $user->setIsCertified((bool)$isCertified);
            $manager->persist($user);
        }
        // flush
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 2;
    }
}