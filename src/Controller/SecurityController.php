<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\VersioningService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
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
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly VersioningService $versioningService
    )
    {
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        // groups
        $groups = ['user:write'];
        // get user and limit to write
        $context = DeserializationContext::create()->setGroups($groups);
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json', $context);
        // validate
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) throw new HttpException(Response::HTTP_BAD_REQUEST, (string) $errors);
        // hash password
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        // save
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // context
        $context = SerializationContext::create()->setGroups(['user:read', 'user:item']);
        $context->setVersion($this->versioningService->getVersion());
        // return
        return new JsonResponse(
            $this->serializer->serialize($user, 'json', $context),
            Response::HTTP_CREATED,
            [],
            true
        );
    }
}