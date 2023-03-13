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
        $categories = [
            "Action",
            "Adventure",
            "Animation",
            "Biography",
            "Comedy",
            "Actualités",
            "Art et culture",
            "Beauté et mode",
            "Cuisine et recettes",
            "Développement personnel",
            "Divertissement",
            "Économie et finance",
            "Éducation",
            "Environnement et écologie",
            "Histoire",
            "Informatique et technologie",
            "Jeux et loisirs",
            "Langues étrangères",
            "Littérature",
            "Musique",
            "Nature et voyage",
            "Politique",
            "Psychologie",
            "Religion et spiritualité",
            "Santé et bien-être",
            "Science",
            "Sport",
            "Télévision et cinéma",
            "Vie professionnelle",
            "Vie quotidienne",
            "Vie sociale et relations",
            "Voyages",
            "Animaux et nature",
            "Automobiles et transports",
            "Science-fiction et fantasy"
        ];
        // for each category, create a new category object and persist it
        for ($i = 0; $i < count($categories); $i++) {
            $category = new Category();
            $category->setName($categories[$i]);
            $manager->persist($category);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 3;
    }
}