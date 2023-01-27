<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    const REMEMBER_ME_EXPIRATION_DAYS = 30;
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        // user data
        $user = $event->getUser();
        if (!$user instanceof User) return;
        $payload['id'] = $user->getId();
        $payload['username'] = $user->getUsername();
        $payload['isVerified'] = $user->isIsVerified();
        $payload['isActive'] = $user->isIsActive();
        $payload['isBanned'] = $user->isIsBanned();
        // remember me
        $request = $this->requestStack->getCurrentRequest();
        $content = json_decode($request->getContent(), true);
        if (isset($content['remember_me']) && $content['remember_me']) {
            $expiration = new \DateTimeImmutable(sprintf('+%d days', self::REMEMBER_ME_EXPIRATION_DAYS));
            $payload['exp'] = $expiration->getTimestamp();
        }
        // update token
        $event->setData($payload);
        // add header
        $header = $event->getHeader();
        $header['cty'] = 'JWT';
        $event->setHeader($header);
    }
}