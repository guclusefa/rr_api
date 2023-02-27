<?php

namespace App\Controller\admin;

use App\Entity\Relation;
use App\Repository\RelationRepository;
use App\Service\SerializerService;
use App\Service\RelationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/admin/relations')]
class RelationController extends AbstractController
{
    public function __construct
    (
        private readonly RelationRepository $relationRepository,
        private readonly SerializerService $serializerService,
        private readonly RelationService $relationService,
        private readonly TranslatorInterface $translator
    )
    {
    }

    #[Route('', name: 'api_admin_relations_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // deserialize, create
        $relation = $this->serializerService->deserialize(Relation::GROUP_WRITE ,$request, Relation::class);
        $this->relationService->create($relation);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.relation.created_success')],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'api_admin_relations_update', methods: ['PUT'])]
    public function update(Request $request, Relation $relation): JsonResponse
    {
        // deserialize & update
        $updatedRelation = $this->serializerService->deserialize(Relation::GROUP_WRITE, $request, Relation::class);
        $this->relationService->update($relation, $updatedRelation);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.relation.updated_success')],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}', name: 'api_admin_relations_delete', methods: ['DELETE'])]
    public function delete(Relation $relation): JsonResponse
    {
        // delete & return
        $this->relationRepository->remove($relation, true);
        return new JsonResponse(
            ['message' => $this->translator->trans('message.relation.deleted_success')],
            Response::HTTP_OK
        );
    }
}