<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\ResourceLike;
use App\Entity\ResourceShare;
use App\Repository\ResourceRepository;
use App\Service\FileUploaderService;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploaderService $fileUploaderService,
        private readonly ResourceRepository $resourceRepository
    )
    {
    }

    // TODO : order by comments, likes etc
    #[Route('', name: 'api_resources', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $search = $request->query->get('search');
        // array of criterias
        $author = $request->query->all('author');
        $relation = $request->query->all('relation');
        $category = $request->query->all('category');
        // pagination
        $order = $request->query->get('order', 'id');
        $direction = $request->query->get('direction', 'ASC');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);


        // get, serialize & return
        $resources = $this->resourceRepository->advanceSearch($search, $author, $relation, $category, $order, $direction, $page, $limit);
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

    #[Route('/{id}/media', name: 'api_resources_update_media', methods: ['POST'])]
    public function addMedia(Request $request, Resource $resource): JsonResponse
    {
        // check & upload media
        $media = $request->files->get('media');
        if ($media) {
            // check and upload media
            $resource->setMedia(
                $this->fileUploaderService->uploadMedia(
                    $media,
                    $resource->getId(),
                    $this->getParameter("app.resource.media.path")
                )
            );
        } else {
            // delete file from server if exists
            $mediaName = $resource->getMedia();
            if ($mediaName) {
                $mediaPath = $this->getParameter("app.resource.media.path") . '/' . $mediaName;
                $this->fileUploaderService->deleteFile($mediaPath);
            }
            $resource->setMedia(null);
        }
        // persist & flush
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'Le media de la ressource a bien été modifié'],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/like', name: 'api_resources_like', methods: ['POST'])]
    public function like(Resource $resource): JsonResponse
    {
        // check if user already liked
        $like = $resource->getLikes()->filter(function ($like) {
            return $like->getUser() === $this->getUser();
        })->first();
        if ($like) {
            $resource->removeLike($like);
            $this->entityManager->remove($like);
            $message = 'Vous avez bien retiré votre like';
        } else {
            $like = new ResourceLike();
            $like->setUser($this->getUser());
            $resource->addLike($like);
            $this->entityManager->persist($like);
            $message = 'Vous avez bien liké cette ressource';
        }
        // persist & flush
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => $message],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/share', name: 'api_resources_share', methods: ['POST'])]
    public function share(Resource $resource): JsonResponse
    {
        // check if user already shared
        $share = $resource->getShares()->filter(function ($share) {
            return $share->getUser() === $this->getUser();
        })->first();
        if ($share) {
            $resource->removeShare($share);
            $this->entityManager->remove($share);
            $message = 'Vous avez bien retiré votre partage';
        } else {
            $share = new ResourceShare();
            $share->setUser($this->getUser());
            $resource->addShare($share);
            $this->entityManager->persist($share);
            $message = 'Vous avez bien partagé cette ressource';
        }
        // persist & flush
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => $message],
            Response::HTTP_OK
        );
    }
}