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
        $relations = [
            "Amour",
            "Famille",
            "Mariage",
            "Divorce",
            "Parentalité",
            "Fratrie",
            "Amitié",
            "Relations amoureuses",
            "Relations familiales",
            "Relations professionnelles",
            "Relations interculturelles",
            "Relations intergénérationnelles",
            "Relations de couple",
            "Relations de travail",
            "Relations sociales",
            "Relations communautaires",
            "Relations de voisinage",
            "Relations de pouvoir",
            "Coopération",
            "Collaboration",
            "Communication",
            "Confiance",
            "Respect",
            "Écoute",
            "Empathie",
            "Soutien",
            "Solidarité",
            "Épanouissement personnel",
            "Développement personnel",
            "Bien-être émotionnel"
        ];
        for ($i = 0; $i < count($relations); $i++) {
            $relation = new Relation();
            $relation->setName($relations[$i]);
            $manager->persist($relation);
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 4;
    }
}