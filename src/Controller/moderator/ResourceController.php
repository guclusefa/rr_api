<?php

namespace App\Controller\moderator;

use App\Entity\Resource;
use App\Repository\ResourceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/moderator/resources')]
class ResourceController extends AbstractController
{
    public function __construct
    (
        private readonly ResourceRepository $resourceRepository,
        private readonly TranslatorInterface $translator
    )
    {
    }

    #[Route('/{id}/verify', name: 'api_resources_verify', methods: ['PUT'])]
    public function verify(Resource $resource): JsonResponse
    {
        // verify
        $isVerified = $resource->isIsVerified();
        $resource->setIsVerified(!$isVerified);
        if ($isVerified) {
            $message = $this->translator->trans('message.resource.unverify_success');
        } else {
            $message = $this->translator->trans('message.resource.verify_success');
        }
        $this->resourceRepository->save($resource, true);
        // return
        return new JsonResponse(
            ['message' => $message],
            Response::HTTP_OK
        );
    }
}