<?php

namespace App\Service;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentService
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly CommentRepository $commentRepository,
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function checkUpdateAccess($comment, $me): void
    {
        if ($comment->getAuthor() !== $me) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $this->translator->trans('message.comment.access_update_denied'));
        }
    }

    public function comment($comment, $user): void
    {
        // create
        $comment->setAuthor($user);
        // check for errors
        $this->serializerService->checkErrors($comment);
        // save
        $this->commentRepository->save($comment, true);
    }

    public function replyTo(Comment $comment, $reply, $user): void
    {
        // create
        $reply->setAuthor($user);
        $reply->setReplyTo($comment);
        $reply->setResource($comment->getResource());
        // check for errors
        $this->serializerService->checkErrors($reply);
        // save
        $this->commentRepository->save($reply, true);
    }
}