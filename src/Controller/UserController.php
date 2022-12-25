<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\SearcherService;
use App\Service\SerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly SearcherService $searcherService
    )
    {
    }

    #[Route('', name: 'api_users', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $fieldsToSearchFrom = ['username', 'firstName', 'lastName'];
        $defaultFilters = ['isVerified' => true, 'isActive' => true, 'isBanned' => false];
        $fieldsToFilterFrom = ['gender', 'state'];
        $fieldsToOrderFrom = ['id','username', 'createdAt'];
        // search by criterias
        $users = $this->searcherService->fullyFilteredData(
            $request->query->all(),
            $fieldsToSearchFrom,
            $defaultFilters,
            $fieldsToFilterFrom,
            $fieldsToOrderFrom,
            User::class
        );
        // serialize & return
        $groupsToSerialize = ['user:read'];
        $users = $this->serializerService->serialize($groupsToSerialize, $users);
        return new JsonResponse($users, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        // serialize & return
        $groupsToSerialize = ['user:read', 'user:item'];
        $user = $this->serializerService->serialize($groupsToSerialize, $user);
        return new JsonResponse($this->serializerService->getSerializedData($user), Response::HTTP_OK, [], true);
    }
}