<?php

namespace App\Service;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\ResourceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentService
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly CommentRepository $commentRepository,
        private readonly TranslatorInterface $translator,
        private readonly ResourceRepository $resourceRepository
    )
    {
    }

    public function checkAccess($resource, $me): void
    {
        // if user is ROLE_ADMIN, allow access
        if ($me->getRoles()[0] === 'ROLE_MODERATOR' || $me->getRoles()[0] === 'ROLE_ADMIN' || $me->getRoles()[0] === 'ROLE_SUPER_ADMIN') {
            return;
        }
        if (!$this->resourceRepository->isAccesibleToMe($resource, $me)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $this->translator->trans('message.comment.access_denied'));
        }
    }

    public function checkUpdateAccess($comment, $me): void
    {
        // if user is ROLE_ADMIN, allow access
        if ($me->getRoles()[0] === 'ROLE_MODERATOR' || $me->getRoles()[0] === 'ROLE_ADMIN' || $me->getRoles()[0] === 'ROLE_SUPER_ADMIN') {
            return;
        }
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

    public function update(Comment $comment, $updatedComment): void
    {
        // update
        $comment->setContent($updatedComment->getContent());
        // check for errors
        $this->serializerService->checkErrors($comment);
        // save
        $this->commentRepository->save($comment, true);
    }
}