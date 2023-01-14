<?php

namespace App\Fixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixture extends Fixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        // 20 categories
        for ($i = 0; $i < 20; $i++) {
            $category = new Category();
            $category->setName($faker->word);
            $manager->persist($category);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 3;
    }
}