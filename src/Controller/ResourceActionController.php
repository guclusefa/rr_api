<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\User;
use App\Repository\ResourceRepository;
use App\Service\ResourceService;
use App\Service\SerializerService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/resources')]
class ResourceActionController extends AbstractController
{
    public function __construct
    (
        private readonly ResourceService $resourceService,
        private readonly SerializerService $serializerService,
        private readonly ResourceRepository $resourceRepository
    )
    {
    }

    #[Route('/{ressourceId}/like/{userId}', name: 'api_resources_is_liked', methods: ['GET'])]
    #[ParamConverter('resource', options: ['id' => 'ressourceId'])]
    #[ParamConverter('user', options: ['id' => 'userId'])]
    public function isLiked(Resource $resource, User $user): JsonResponse
    {
        $isLiked = $this->resourceService->isLiked($resource, $user);
        return new JsonResponse(
            $isLiked,
            Response::HTTP_OK,
        );
    }


    #[Route('/{ressourceId}/share/{userId}', name: 'api_resources_is_shared', methods: ['GET'])]
    #[ParamConverter('resource', options: ['id' => 'ressourceId'])]
    #[ParamConverter('user', options: ['id' => 'userId'])]
    public function isShared(Resource $resource, User $user): JsonResponse
    {
        $isShared = $this->resourceService->isShared($resource, $user);
        return new JsonResponse(
            $isShared,
            Response::HTTP_OK,
        );
    }

    #[Route('/{ressourceId}/exploit/{userId}', name: 'api_resources_is_exploited', methods: ['GET'])]
    #[ParamConverter('resource', options: ['id' => 'ressourceId'])]
    #[ParamConverter('user', options: ['id' => 'userId'])]
    public function isExploited(Resource $resource, User $user): JsonResponse
    {
        $isExploited = $this->resourceService->isExploited($resource, $user);
        return new JsonResponse(
            $isExploited,
            Response::HTTP_OK,
        );
    }

    #[Route('/{ressourceId}/save/{userId}', name: 'api_resources_is_saved', methods: ['GET'])]
    #[ParamConverter('resource', options: ['id' => 'ressourceId'])]
    #[ParamConverter('user', options: ['id' => 'userId'])]
    public function isSaved(Resource $resource, User $user): JsonResponse
    {
        $isSaved = $this->resourceService->isSaved($resource, $user);
        return new JsonResponse(
            $isSaved,
            Response::HTTP_OK,
        );
    }

    #[Route('/{ressourceId}/consult/{userId}', name: 'api_resources_is_consulted', methods: ['GET'])]
    #[ParamConverter('resource', options: ['id' => 'ressourceId'])]
    #[ParamConverter('user', options: ['id' => 'userId'])]
    public function isConsulted(Resource $resource, User $user): JsonResponse
    {
        $isConsulted = $this->resourceService->isConsulted($resource, $user);
        return new JsonResponse(
            $isConsulted,
            Response::HTTP_OK,
        );
    }
}