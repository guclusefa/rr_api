<?php

namespace App\Controller\admin;

use App\Entity\State;
use App\Repository\StateRepository;
use App\Service\SerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/states')]
#[IsGranted('ROLE_ADMIN')]
class StateController extends AbstractController
{
    public function __construct
    (
        private readonly StateRepository $stateRepository,
        private readonly SerializerService $serializerService
    )
    {
    }

    #[Route('', name: 'api_admin_states', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // get, serialize & return
        $states = $this->stateRepository->findAll();
        $states = $this->serializerService->serialize(State::GROUP_GET, $states);
        return new JsonResponse(
            $states,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_admin_states_show', methods: ['GET'])]
    public function show(State $state): JsonResponse
    {
        // get, serialize & return
        $state = $this->serializerService->serialize(State::GROUP_ITEM, $state);
        return new JsonResponse(
            $state,
            Response::HTTP_OK,
            [],
            true
        );
    }
    // TODO
    #[Route('', name: 'api_admin_states_create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        // create & return
        $state = new State();
        $this->stateRepository->save($state, true);
        return new JsonResponse(
            null,
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'api_admin_states_update', methods: ['PUT'])]
    public function update(State $state): JsonResponse
    {
        // update & return
        $this->stateRepository->save($state, true);
        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    #[Route('/{id}', name: 'api_admin_states_delete', methods: ['DELETE'])]
    public function delete(State $state): JsonResponse
    {
        // delete & return
        $this->stateRepository->remove($state, true);
        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }
}