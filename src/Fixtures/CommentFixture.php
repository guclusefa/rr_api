<?php

namespace App\Fixtures;

use App\Entity\Comment;
use App\Repository\ResourceRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixture extends Fixture implements OrderedFixtureInterface
{
    public function __construct
    (
        private readonly UserRepository $userRepository,
        private readonly ResourceRepository $resourceRepository
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');
        $users = $this->userRepository->findAll();
        $resources = $this->resourceRepository->findAll();
        // 100 comments
        for ($i = 0; $i < 100; $i++) {
            $comment = new Comment();
            $comment->setResource($resources[array_rand($resources)]);
            $comment->setAuthor($users[array_rand($users)]);
            $comment->setContent("Commentaire {$i}");
            $manager->persist($comment);
            // 1 to 5 replies
            for ($j = 0; $j < $faker->numberBetween(1, 5); $j++) {
                $reply = new Comment();
                $reply->setResource($comment->getResource());
                $reply->setAuthor($users[array_rand($users)]);
                $reply->setContent("Réponse {$j}");
                $reply->setReplyTo($comment);
                $manager->persist($reply);
            }
        }
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 6;
    }
}