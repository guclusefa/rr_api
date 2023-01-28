<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class JWTDecodedListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
        // needed because of locale listener
        private readonly ParameterBagInterface $params
    )
    {
    }
    public function onJWTDecoded(JWTDecodedEvent $event): void
    {
        // set locale because dosent work for some reason in LocaleListener : needs to be fixed
        $request = $this->requestStack->getCurrentRequest();
        if ($request->headers->has('Accept-Language')) {
            $locale = $request->headers->get('Accept-Language');
            if (in_array($locale, $this->params->get('app.supported_locales'))) {
                $this->translator->setLocale($request->headers->get('Accept-Language'));
            }
        }

        $payload = $event->getPayload();
        // if user is banned
        $user = $this->entityManager->getRepository(User::class)->find($payload['id']);
        if ($user !== null && $this->userRepository->isBanned($user)) {
            // get ban reason and end date
            $ban = $this->userRepository->getMostCurrentBan($user);
            $endDate = $ban->getEndDate();
            $endDate = $endDate?->format('Y-m-d');
            $reason = $ban->getReason();
            // throw exception with the corresponding message
            if ($endDate == null) {
                $message = 'message.security.banned_perm';
            } else {
                $message = 'message.security.banned';
            }
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $this->translator->trans($message, ['%date%' => $endDate, '%reason%' => $reason])
            );
        }
    }
}