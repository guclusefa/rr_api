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
        // search fields
        $fieldsToSearchFrom = ['firstName', 'lastName', 'username'];
        // filter fields
        $defaultFilters = ['isVerified' => false];
        $fieldsToFilterFrom = ['state'];
        // order fields
        $fieldsToOrderFrom = ['id','username'];
        // search by criterias
        $users = $this->searcherService->advanceSearch(
            $request->query->all(),
            $fieldsToSearchFrom,
            $defaultFilters,
            $fieldsToFilterFrom,
            $fieldsToOrderFrom,
            User::class
        );
        // serialize
        $groupsToSerialize = ['user:read'];
        $users = $this->serializerService->serialize($groupsToSerialize, $users);
        // send response
        return new JsonResponse($users, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        // serialize
        $groupsToSerialize = ['user:read', 'user:item'];
        $user = $this->serializerService->serialize($groupsToSerialize, $user);
        // send response
        return new JsonResponse($user, Response::HTTP_OK, [], true);
    }
}