<?php

namespace App\Controller\user;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Service\CommentService;
use App\Service\ResourceService;
use App\Service\SerializerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/comments')]
class CommentController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly CommentRepository $commentRepository,
        private readonly CommentService $commentService,
        private readonly TranslatorInterface $translator
    )
    {
    }

    #[Route('', name: 'api_comments', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $search = $request->query->get('search');
        // array of criterias
        $author = $request->query->all('author');
        $resource = $request->query->all('resource');
        $replyto = $request->query->all('replyto');
        $isnotreply = $request->query->get('isnotreply');
        // pagination
        $order = $request->query->get('order', 'id');
        $direction = $request->query->get('direction', 'ASC');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        // get, serialize & return
        $comments = $this->commentRepository->advanceSearch(
            $this->getUser(),
            $search,
            $author, $resource, $replyto, $isnotreply,
            $order, $direction, $page, $limit
        );
        $comments = $this->serializerService->serialize(Comment::GROUP_GET, $comments);
        return new JsonResponse(
            $comments,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_comments_show', methods: ['GET'])]
    public function show(Comment $comment): JsonResponse
    {
        // check access
        $this->commentService->checkAccess($comment->getResource(), $this->getUser());
        // serialize
        $comment = $this->serializerService->serialize(Comment::GROUP_ITEM, $comment);
        return new JsonResponse(
            $this->serializerService->getSerializedData($comment),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('', name: 'api_comments_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // deserialize check, & comment
        $comment = $this->serializerService->deserialize(Comment::GROUP_WRITE ,$request, Comment::class);
        $this->commentService->checkAccess($comment->getResource(), $this->getUser());
        $this->commentService->comment($comment, $this->getUser());
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.comment.created_success')],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}/reply', name: 'api_comments_reply', methods: ['POST'])]
    public function reply(Request $request, Comment $comment): JsonResponse
    {
        // check access
        $this->commentService->checkAccess($comment->getResource(), $this->getUser());
        // deserialize & reply
        $reply = $this->serializerService->deserialize(Comment::GROUP_REPLY ,$request, Comment::class);
        $this->commentService->replyTo($comment, $reply, $this->getUser());
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.comment.reply_success')],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'api_comments_delete', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(Comment $comment): JsonResponse
    {
        // check access
        $this->commentService->checkUpdateAccess($comment, $this->getUser());
        // delete user
        $this->commentRepository->remove($comment, true);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.comment.deleted_success')],
            Response::HTTP_OK
        );
    }
}