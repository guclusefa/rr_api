<?php

namespace App\Controller\user;

use App\Entity\Resource;
use App\Entity\ResourceConsult;
use App\Entity\ResourceExploit;
use App\Entity\ResourceLike;
use App\Entity\ResourceSave;
use App\Entity\ResourceShare;
use App\Entity\ResourceSharedTo;
use App\Entity\User;
use App\Repository\ResourceConsultRepository;
use App\Repository\ResourceExploitRepository;
use App\Repository\ResourceLikeRepository;
use App\Repository\ResourceSaveRepository;
use App\Repository\ResourceSharedToRepository;
use App\Repository\ResourceShareRepository;
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

    // shares
    #[Route('/shares', name: 'api_resources_shares', methods: ['GET'])]
    public function getShares(Request $request, ResourceShareRepository $resourceShareRepository): JsonResponse
    {
        // by resource
        $resource = $request->query->get('resource');
        // get page and limit
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        // get shares
        $shares = $resourceShareRepository->advanceSearch($resource, $page, $limit);
        $shares = $this->serializerService->serialize(ResourceShare::GROUP_GET, $shares);
        return new JsonResponse(
            $shares,
            Response::HTTP_OK,
            [],
            true
        );
    }

    // likes
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

    // exploits
    #[Route('/exploits', name: 'api_resources_exploits', methods: ['GET'])]
    public function getExploits(Request $request, ResourceExploitRepository $resourceExploitRepository): JsonResponse
    {
        // by resource
        $resource = $request->query->get('resource');
        // get page and limit
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        // get exploits
        $exploits = $resourceExploitRepository->advanceSearch($resource, $page, $limit);
        $exploits = $this->serializerService->serialize(ResourceExploit::GROUP_GET, $exploits);
        return new JsonResponse(
            $exploits,
            Response::HTTP_OK,
            [],
            true
        );
    }

    // saves
    #[Route('/saves', name: 'api_resources_saves', methods: ['GET'])]
    public function getSaves(Request $request, ResourceSaveRepository $resourceSaveRepository): JsonResponse
    {
        // by resource
        $resource = $request->query->get('resource');
        // get page and limit
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        // get saves
        $saves = $resourceSaveRepository->advanceSearch($resource, $page, $limit);
        $saves = $this->serializerService->serialize(ResourceSave::GROUP_GET, $saves);
        return new JsonResponse(
            $saves,
            Response::HTTP_OK,
            [],
            true
        );
    }

    // consults
    #[Route('/consults', name: 'api_resources_consults', methods: ['GET'])]
    public function getConsults(Request $request, ResourceConsultRepository $resourceConsultRepository): JsonResponse
    {
        // by resource
        $resource = $request->query->get('resource');
        // get page and limit
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        // get consults
        $consults = $resourceConsultRepository->advanceSearch($resource, $page, $limit);
        $consults = $this->serializerService->serialize(ResourceConsult::GROUP_GET, $consults);
        return new JsonResponse(
            $consults,
            Response::HTTP_OK,
            [],
            true
        );
    }

    // sharesto
    #[Route('/sharesto', name: 'api_resources_sharesto', methods: ['GET'])]
    public function getSharesTo(Request $request, ResourceSharedToRepository $resourceSharedTo): JsonResponse
    {
        // by resource
        $resource = $request->query->get('resource');
        // get page and limit
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        // get consults
        $sharesto = $resourceSharedTo->advanceSearch($resource, $page, $limit);
        $sharesto = $this->serializerService->serialize(ResourceSharedTo::GROUP_GET, $sharesto);
        return new JsonResponse(
            $sharesto,
            Response::HTTP_OK,
            [],
            true
        );
    }
}