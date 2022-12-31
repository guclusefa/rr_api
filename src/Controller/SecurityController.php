<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class SecurityController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ValidatorInterface $validator,
    )
    {
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {
        // this method is never called, it's just here to satisfy the security system
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        // deserialize & validate
        $groupsToDeserialize = ['user:register'];
        $user = $this->serializerService->deserialize($groupsToDeserialize ,$request, User::class);
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) throw new HttpException(Response::HTTP_BAD_REQUEST, (string) $errors);
        // save and persist
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // serialize & return
        $groupsToSerialize = ['user:read', 'user:item'];
        $user = $this->serializerService->serialize($groupsToSerialize, $user);
        return new JsonResponse($user, Response::HTTP_CREATED, [], true);
    }
}