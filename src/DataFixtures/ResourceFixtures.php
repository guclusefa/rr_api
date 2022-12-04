<?php

namespace App\DataFixtures;

use App\Entity\Resource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ResourceFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        // 20 resources
        for ($i = 0; $i < 20; $i++) {
            $resource = new Resource();
            $resource->setTitle("Resource ${i}");
            $resource->setContent("Content ${i}");
            $manager->persist($resource);
        }
        $manager->flush();
    }
}
