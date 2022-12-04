<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api/resources')]
class ResourceController extends AbstractController
{
    public function __construct(
        private readonly ResourceRepository $resourceRepository,
        private readonly SerializerInterface  $serializer,
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('', name: 'api_resources', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // pagination
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        // get all resources
        $resources = $this->resourceRepository->findAllWithPagination($page, $limit);
        return new JsonResponse(
            $this->serializer->serialize($resources, 'json', [
                AbstractNormalizer::GROUPS => ['resource:read'],
            ]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_resources_show', methods: ['GET'])]
    public function show(Resource $resource): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($resource, 'json' , [
                AbstractNormalizer::GROUPS => ['resource:read'],
            ]),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('', name: 'api_resources_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, ValidatorInterface $validator): JsonResponse
    {
        // get the data from the request
        $resource = $this->serializer->deserialize($request->getContent(), Resource::class, 'json');
        // validate the data
        $errors = $validator->validate($resource);
        if (count($errors) > 0) throw new HttpException(Response::HTTP_BAD_REQUEST, (string) $errors);
        // save the resource
        $this->entityManager->persist($resource);
        $this->entityManager->flush();
        // return the resource
        return new JsonResponse(
            $this->serializer->serialize($resource, 'json'),
            Response::HTTP_CREATED,
            ["Location" => $this->generateUrl('api_resources_show', ['id' => $resource->getId()])],
            true
        );
    }

    #[Route('/{id}', name: 'api_resources_update', methods: ['PUT'])]
    public function update(Resource $resource, Request $request): JsonResponse
    {
        $this->serializer->deserialize($request->getContent(), Resource::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $resource]);
        $this->entityManager->flush();

        return new JsonResponse(
            $this->serializer->serialize($resource, 'json'),
            Response::HTTP_OK,
            ["Location" => $this->generateUrl('api_resources_show', ['id' => $resource->getId()])],
            true
        );
    }

    #[Route('/{id}', name: 'api_resources_delete', methods: ['DELETE'])]
    public function delete(Resource $resource): JsonResponse
    {
        $this->entityManager->remove($resource);
        $this->entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
