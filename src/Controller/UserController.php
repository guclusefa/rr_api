<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ResourceRepository;
use App\Repository\UserRepository;
use App\Service\VersioningService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SerializerInterface  $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly VersioningService $versioningService
    )
    {
    }

    #[Route('', name: 'api_users', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // pagination
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        // groups
        $groups = ['user:read'];
        // context
        $context = SerializationContext::create()->setGroups($groups);
        $context->setVersion($this->versioningService->getVersion());
        // get users
        $users = $this->userRepository->findAllWithPagination($page, $limit);
        return new JsonResponse(
            $this->serializer->serialize($users, 'json', $context),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        // groups
        $groups = ['user:read', 'user:item', 'resource:read'];
        // if admin or if user is me, add confidential data
        if ($this->getUser() === $user || $this->isGranted('ROLE_ADMIN')) {
            $groups[] = 'user:confidential';
        }
        $context = SerializationContext::create()->setGroups($groups);
        $context->setVersion($this->versioningService->getVersion());
        return new JsonResponse(
            $this->serializer->serialize($user, 'json', $context),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        // if not admin or not my account, throw exception
        if ($this->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'You are not authorized to delete this user');
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}