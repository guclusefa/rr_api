<?php

namespace App\Fixtures;

use App\Entity\Resource;
use App\Entity\ResourceConsult;
use App\Entity\ResourceExploit;
use App\Entity\ResourceLike;
use App\Entity\ResourceShare;
use App\Repository\CategoryRepository;
use App\Repository\RelationRepository;
use App\Repository\ResourceRepository;
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
        private readonly RelationRepository $relationRepository,
        private readonly ResourceRepository $resourceRepository
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $users = $this->userRepository->findAll();
        $categories = $this->categoryRepository->findAll();
        $relations = $this->relationRepository->findAll();

        // 100 resources
        for ($i = 0; $i < 100; $i++) {
            $resource = new Resource();
            $resource->setAuthor($users[array_rand($users)]);
            $resource->setRelation($relations[array_rand($relations)]);
            $resource->setTitle("Ressource $i");
            $resource->setContent("Contenu {$i}");
            $resource->setVisibility(1);
            $resource->setIsVerified(rand(0, 1));
            for($j = 0; $j < $faker->numberBetween(1,5); $j++) {
                $resource->addCategory($categories[array_rand($categories)]);
            }

            $manager->persist($resource);
        }
        $manager->flush();

        $resources = $this->resourceRepository->findAll();
        foreach ($resources as $resource) {
            // random number of shares
            $random = rand(0, 100);
            for ($i = 0; $i < $random; $i++) {
                $share = new ResourceShare();
                $share->setResource($resource);
                $share->setUser($users[array_rand($users)]);
                $manager->persist($share);
            }
            // random number of likes
            $random = rand(0, 100);
            for ($i = 0; $i < $random; $i++) {
                $like = new ResourceLike();
                $like->setResource($resource);
                $like->setUser($users[array_rand($users)]);
                $manager->persist($like);
            }
            // random number of explois
            $random = rand(0, 100);
            for ($i = 0; $i < $random; $i++) {
                $exploit = new ResourceExploit();
                $exploit->setResource($resource);
                $exploit->setUser($users[array_rand($users)]);
                $manager->persist($exploit);
            }
            // random number of consultations
            $random = rand(0, 100);
            for ($i = 0; $i < $random; $i++) {
                $consult = new ResourceConsult();
                $consult->setResource($resource);
                $consult->setUser($users[array_rand($users)]);
                $manager->persist($consult);
            }
            $manager->flush();
        }
    }

    public function getOrder(): int
    {
        return 5;
    }
}