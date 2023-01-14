<?php

namespace App\Fixtures;

use App\Entity\Resource;
use App\Repository\CategoryRepository;
use App\Repository\RelationRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ResourceFixture extends Fixture implements OrderedFixtureInterface
{
    public function __construct
    (
        private readonly UserRepository $userRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly RelationRepository $relationRepository
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $users = $this->userRepository->findAll();
        $categories = $this->categoryRepository->findAll();
        $relations = $this->relationRepository->findAll();

        // 20 resources
        for ($i = 0; $i < 20; $i++) {
            $resource = new Resource();
            $resource->setAuthor($users[array_rand($users)]);
            $resource->setRelation($relations[array_rand($relations)]);
            $resource->setTitle("Ressource $i");
            $resource->setContent("Contenu {$i}");
            $resource->setLink("https://www.google.com");
            $resource->setVisibility($faker->numberBetween(1,3));
            for($j = 0; $j < $faker->numberBetween(1,5); $j++) {
                $resource->addCategory($categories[array_rand($categories)]);
            }

            $manager->persist($resource);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 5;
    }
}