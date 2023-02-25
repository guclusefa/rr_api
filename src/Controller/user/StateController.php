<?php

namespace App\Controller\user;

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

#[Route('/api/states')]
class StateController extends AbstractController
{
    public function __construct
    (
        private readonly StateRepository $stateRepository,
        private readonly SerializerService $serializerService,
    )
    {
    }

    #[Route('', name: 'api_admin_states', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $search = $request->query->get('search');
        // pagination
        $order = $request->query->get('order', 'id');
        $direction = $request->query->get('direction', 'ASC');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        // get, serialize & return
        $states = $this->stateRepository->advanceSearch($search, $order, $direction, $page, $limit);
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
}