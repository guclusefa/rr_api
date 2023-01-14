<?php

namespace App\Fixtures;

use App\Entity\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class StateFixture extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        // create 20 states
        for ($i = 0; $i < 20; $i++) {
            $state = new State();
            $state->setCode($faker->countryCode);
            $state->setName($faker->word);
            $manager->persist($state);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}