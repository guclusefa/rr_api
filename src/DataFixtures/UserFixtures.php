<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\StateRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements OrderedFixtureInterface
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
        // create 1 user
        $user = new User();
        $user->setEmail("user@gmail.com");
        $user->setUsername("user");
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $manager->persist($user);

        // create 1 admin
        $admin = new User();
        $admin->setEmail("admin@gmail.com");
        $admin->setUsername("admin");
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        // create 20 random users
        $faker = \Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail($faker->email);
            $user->setUsername($faker->userName);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
            $user->setRoles(['ROLE_USER']);
            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setMobile($faker->phoneNumber);
            $user->setGender($faker->word[0]);
            $user->setBio("bio ${i}");
            $user->setBirthDate($faker->dateTimeBetween('-50 years', '-18 years'));
            // state
            $states = $this->stateRepository->findAll();
            $user->setState($states[array_rand($states)]);
            // random photo
            $user->setPhoto("https://picsum.photos/200/300?random=${i}");
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
