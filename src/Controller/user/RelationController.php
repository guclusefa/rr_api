<?php

namespace App\Controller\user;

use App\Entity\Relation;
use App\Repository\RelationRepository;
use App\Service\SerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/relations')]
class RelationController extends AbstractController
{
    public function __construct
    (
        private readonly RelationRepository $relationRepository,
        private readonly SerializerService $serializerService,
    )
    {
    }

    #[Route('', name: 'api_admin_relations', methods: ['GET'])]
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
        $relations = $this->relationRepository->advanceSearch($search, $order, $direction, $page, $limit);
        $relations = $this->serializerService->serialize(Relation::GROUP_GET, $relations);
        return new JsonResponse(
            $relations,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_admin_relations_show', methods: ['GET'])]
    public function show(Relation $relation): JsonResponse
    {
        // get, serialize & return
        $relation = $this->serializerService->serialize(Relation::GROUP_ITEM, $relation);
        return new JsonResponse(
            $relation,
            Response::HTTP_OK,
            [],
            true
        );
    }
}