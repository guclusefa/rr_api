<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\UserBan;
use App\Repository\UserBanRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class JWTDecodedListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository
    )
    {
    }
    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $payload = $event->getPayload();

        // if user is banned
        $user = $this->entityManager->getRepository(User::class)->find($payload['id']);
        if ($user !== null && $this->userRepository->isBanned($user)) {
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $this->translator->trans('message.security.banned')
            );
        }
    }
}