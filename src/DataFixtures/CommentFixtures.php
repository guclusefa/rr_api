<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Repository\ResourceRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements OrderedFixtureInterface
{
    public function __construct
    (
        private readonly UserRepository $userRepository,
        private readonly ResourceRepository $resourceRepository,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        // add 20 comments to random resources
        for ($i = 0; $i < 20; $i++) {
            $comment = new Comment();
            $comment->setContent("Comment ${i}");
            // random author
            $authors = $this->userRepository->findAll();
            $comment->setAuthor($authors[array_rand($authors)]);
            // random resource
            $resources = $this->resourceRepository->findAll();
            $comment->setResource($resources[array_rand($resources)]);
            $manager->persist($comment);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 6;
    }
}
