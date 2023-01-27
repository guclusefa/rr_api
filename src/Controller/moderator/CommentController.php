<?php

namespace App\Controller\moderator;

use App\Entity\Comment;
use App\Repository\ResourceRepository;
use App\Service\CommentService;
use App\Service\ResourceService;
use App\Service\SerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/moderator/comments')]
class CommentController extends AbstractController
{
    public function __construct
    (
        private readonly TranslatorInterface $translator,
        private readonly CommentService $commentService,
        private readonly SerializerService $serializerService
    )
    {
    }
    #[Route('/{id}', name: 'api_comments_update', methods: ['PUT'])]
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // deserialize & update
        $updatedComment = $this->serializerService->deserialize(Comment::GROUP_UPDATE, $request, Comment::class);
        $this->commentService->update($comment, $updatedComment);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.comment.updated_success')],
            Response::HTTP_OK
        );
    }
}