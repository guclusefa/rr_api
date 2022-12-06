<?php

namespace App\DataFixtures;

use App\Entity\Resource;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\RelationRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

// order of fixtures loading
class ResourceFixtures extends Fixture implements OrderedFixtureInterface
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
        // 20 resources
        for ($i = 0; $i < 20; $i++) {
            $resource = new Resource();
            $resource->setTitle($faker->word);
            $resource->setContent("Content ${i}");
            $resource->setVisibility($faker->numberBetween(1, 3));
            $resource->setPhoto($faker->imageUrl(640, 480, 'cats'));
            // crated by random user from database
            $users = $this->userRepository->findAll();
            $resource->setAuthor($users[array_rand($users)]);
            // random number of categories
            $categories = $this->categoryRepository->findAll();
            // 3 categories max
            $nbCategories = $faker->numberBetween(1, 3);
            for ($j = 0; $j < $nbCategories; $j++) {
                $resource->addCategory($categories[array_rand($categories)]);
            }
            // relation
            $relations = $this->relationRepository->findAll();
            $resource->setRelation($relations[array_rand($relations)]);
            $manager->persist($resource);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 5;
    }
}
