<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $user = $event->getUser();
        $payload['id'] = $user->getId();
        $payload['username'] = $user->getUsername();
        $payload['isVerified'] = $user->isIsVerified();
        $payload['isActive'] = $user->isIsActive();
        $payload['isBanned'] = $user->isIsBanned();
        $event->setData($payload);

        $header = $event->getHeader();
        $header['cty'] = 'JWT';
        $event->setHeader($header);
    }
}