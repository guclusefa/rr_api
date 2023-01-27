<?php

namespace App\Controller\admin;

use App\Entity\State;
use App\Repository\StateRepository;
use App\Service\SerializerService;
use App\Service\StateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/admin/states')]
class StateController extends AbstractController
{
    public function __construct
    (
        private readonly StateRepository $stateRepository,
        private readonly SerializerService $serializerService,
        private readonly StateService $stateService,
        private readonly TranslatorInterface $translator
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

    #[Route('', name: 'api_admin_states_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // deserialize, create
        $state = $this->serializerService->deserialize(State::GROUP_WRITE ,$request, State::class);
        $this->stateService->create($state);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.state.created_success')],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'api_admin_states_update', methods: ['PUT'])]
    public function update(Request $request, State $state): JsonResponse
    {
        // deserialize & update
        $updatedState = $this->serializerService->deserialize(State::GROUP_WRITE, $request, State::class);
        $this->stateService->update($state, $updatedState);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.state.updated_success')],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'api_admin_states_delete', methods: ['DELETE'])]
    public function delete(State $state): JsonResponse
    {
        // delete & return
        $this->stateRepository->remove($state, true);
        return new JsonResponse(
            ['message' => $this->translator->trans('message.state.deleted_success')],
            Response::HTTP_OK
        );
    }
}