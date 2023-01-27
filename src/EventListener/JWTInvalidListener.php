<?php

namespace App\EventListener;


use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class JWTInvalidListener
{
    public function __construct
    (
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function onJWTInvalid(JWTInvalidEvent $event)
    {
         throw new HttpException(
             Response::HTTP_UNAUTHORIZED,
             $this->translator->trans('message.jwt.invalid_event')
         );
    }

    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        throw new HttpException(
            Response::HTTP_UNAUTHORIZED,
            $this->translator->trans('message.jwt.not_found_event')
        );
    }

    public function onJWTExpired(JWTExpiredEvent $event)
    {
        throw new HttpException(
            Response::HTTP_UNAUTHORIZED,
            $this->translator->trans('message.jwt.expired_event')
        );
    }
}