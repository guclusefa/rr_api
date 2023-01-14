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

        // create 1 admin
        $admin = new User();
        $admin->setEmail("admin@gmail.com");
        $admin->setUsername("admin");
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'));
        $admin->setFirstName("admin");
        $admin->setLastName("admin");
        $admin->setGender("M");
        $admin->setBio("bio admin");
        $admin->setBirthDate(new \DateTime("2002-11-15"));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setState($states[array_rand($states)]);
        $manager->persist($admin);

        // create 20 random users
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