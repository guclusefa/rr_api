<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Repository\ResourceRepository;
use App\Repository\UserRepository;
use App\Service\VersioningService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/resources')]
class ResourceController extends AbstractController
{
    public function __construct(
        private readonly ResourceRepository $resourceRepository,
        private readonly UserRepository $userRepository,
        private readonly SerializerInterface  $serializer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly VersioningService $versioningService
    )
    {
    }

    #[Route('', name: 'api_resources', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // pagination
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        // context
        $context = SerializationContext::create()->setGroups(['resource:read']);
        $context->setVersion($this->versioningService->getVersion());
        // get resources
        $resources = $this->resourceRepository->findAllWithPagination($page, $limit);
        return new JsonResponse(
            $this->serializer->serialize($resources, 'json', $context),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_resources_show', methods: ['GET'])]
    public function show(Resource $resource): JsonResponse
    {
        // context
        $context = SerializationContext::create()->setGroups(['resource:read']);
        $context->setVersion($this->versioningService->getVersion());
        // get resource
        return new JsonResponse(
            $this->serializer->serialize($resource, 'json', $context),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('', name: 'api_resources_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        // get the data from the request
        $resource = $this->serializer->deserialize($request->getContent(), Resource::class, 'json');
        // validate the data
        $errors = $this->validator->validate($resource);
        if (count($errors) > 0) throw new HttpException(Response::HTTP_BAD_REQUEST, (string) $errors);
        // save the resource
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
        // return the resource
        return new JsonResponse(
            $this->serializer->serialize($resource, 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_resources_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function update(Resource $resource, Request $request): JsonResponse
    {
        // get the data from the request
        $newResource = $this->serializer->deserialize($request->getContent(), Resource::class, 'json');
        // update the current resource
        $resource->setTitle($newResource->getTitle());
        $resource->setContent($newResource->getContent());
        // check for errors
        $errors = $this->validator->validate($resource);
        if (count($errors) > 0) throw new HttpException(Response::HTTP_BAD_REQUEST, (string) $errors);
        // author
        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;
        $resource->setAuthor($this->userRepository->find($idAuthor));
        // save the resource
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
        // return the resource
        return new JsonResponse(
            $this->serializer->serialize($resource, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_resources_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Resource $resource): JsonResponse
    {
        $this->entityManager->remove($resource);
        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
