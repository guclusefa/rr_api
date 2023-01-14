<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\User;
use App\Service\FileUploaderService;
use App\Service\SearcherService;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/resources')]
class ResourceController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly SearcherService $searcherService,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploaderService $fileUploaderService
    )
    {
    }

    // TODO : order by comments, likes etc
    #[Route('', name: 'api_resources', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $fieldsToSearchFrom = ['title', 'content'];
        $defaultFilters = ['isPublished' => true, 'isSuspended' => false, 'visibility' => 1];
        $fieldsToFilterFrom = ['author', 'relation'];
        $fieldsToOrderFrom = ['id','title', 'createdAt', 'updatedAt'];
        // search by criterias
        $resources = $this->searcherService->fullyFilteredData(
            $request->query->all(),
            $fieldsToSearchFrom,
            $defaultFilters,
            $fieldsToFilterFrom,
            $fieldsToOrderFrom,
            Resource::class
        );
        // serialize & return
        $resources = $this->serializerService->serialize(Resource::GROUP_GET, $resources);
        return new JsonResponse(
            $resources,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_resources_show', methods: ['GET'])]
    public function show(Resource $resource): JsonResponse
    {
        // serialize
        $resource = $this->serializerService->serialize(Resource::GROUP_ITEM, $resource);
        // return
        return new JsonResponse(
            $this->serializerService->getSerializedData($resource),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('', name: 'api_resources_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // deserialize
        $resource = $this->serializerService->deserialize(Resource::GROUP_WRITE ,$request, Resource::class);
        $resource->setAuthor($this->getUser());
        // check for errors
        $this->serializerService->checkErrors($resource);
        // save and persist
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'La ressource a bien été créée'],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'api_resources_update', methods: ['PUT'])]
    public function update(Request $request, Resource $resource): JsonResponse
    {
        // deserialize
        $updatedResource = $this->serializerService->deserialize(Resource::GROUP_UPDATE, $request, Resource::class);
        // update resource
        $resource->setTitle($updatedResource->getTitle());
        $resource->setContent($updatedResource->getContent());
        $resource->setLink($updatedResource->getLink());
        $resource->setVisibility($updatedResource->getVisibility());
        $resource->setIsPublished($updatedResource->isIsPublished());
        $resource->setRelation($updatedResource->getRelation());
        $resource->updateCategories($updatedResource->getCategories());
        // check for errors
        $this->serializerService->checkErrors($resource);
        // save and persist
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'La ressource a bien été modifiée'],
            Response::HTTP_OK
        );
    }
}