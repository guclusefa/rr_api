<?php

namespace App\Controller\user;

use App\Entity\Resource;
use App\Entity\ResourceLike;
use App\Entity\User;
use App\Repository\ResourceLikeRepository;
use App\Service\ResourceService;
use App\Service\SerializerService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ResourceStatsController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
    )
    {
    }

    #[Route('/likes', name: 'api_resources_likes', methods: ['GET'])]
    public function getLikes(Request $request, ResourceLikeRepository $resourceLikeRepository): JsonResponse
    {
        // by resource
        $resource = $request->query->get('resource');
        // get page and limit
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        // get likes
        $likes = $resourceLikeRepository->advanceSearch($resource, $page, $limit);
        $likes = $this->serializerService->serialize(ResourceLike::GROUP_GET, $likes);
        return new JsonResponse(
            $likes,
            Response::HTTP_OK,
            [],
            true
        );
    }
}