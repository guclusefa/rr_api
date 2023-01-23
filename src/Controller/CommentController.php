<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Service\SearcherService;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/comments')]
class CommentController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly SearcherService $searcherService,
        private readonly EntityManagerInterface $entityManager,
        private readonly CommentRepository $commentRepository
    )
    {
    }

    // TODO
    #[Route('', name: 'api_comments', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $search = $request->query->get('search');
        // array of criterias
        $author = $request->query->all('author');
        $resource = $request->query->all('resource');
        $replyto = $request->query->all('replyto');
        // pagination
        $order = $request->query->get('order', 'id');
        $direction = $request->query->get('direction', 'ASC');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        // get, serialize & return
        $comments = $this->commentRepository->advanceSearch($search, $author, $resource, $replyto, $order, $direction, $page, $limit);
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
        // serialize
        $comment = $this->serializerService->serialize(Comment::GROUP_ITEM, $comment);
        return new JsonResponse(
            $comment,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('', name: 'api_comments_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // deserialize
        $comment = $this->serializerService->deserialize(Comment::GROUP_WRITE ,$request, Comment::class);
        $comment->setAuthor($this->getUser());
        // check for errors
        $this->serializerService->checkErrors($comment);
        // save and persist
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'Le commentaire a bien été créé'],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}/reply', name: 'api_comments_reply', methods: ['POST'])]
    public function reply(Request $request, Comment $comment): JsonResponse
    {
        // deserialize
        $reply = $this->serializerService->deserialize(Comment::GROUP_REPLY ,$request, Comment::class);
        $reply->setAuthor($this->getUser());
        $reply->setReplyTo($comment);
        $reply->setResource($comment->getResource());
        // check for errors
        $this->serializerService->checkErrors($reply);
        // save and persist
        $this->entityManager->persist($reply);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'Le commentaire a bien été créé'],
            Response::HTTP_CREATED
        );
    }
}