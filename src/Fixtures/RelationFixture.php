<?php

namespace App\Fixtures;

use App\Entity\Relation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RelationFixture extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        // 20 relations
        for ($i = 0; $i < 20; $i++) {
            $relation = new Relation();
            $relation->setName($faker->word);
            $manager->persist($relation);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 4;
    }
}