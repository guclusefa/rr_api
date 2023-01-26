<?php

namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct
    (
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        if ($user->isIsBanned()) {
            throw new CustomUserMessageAccountStatusException(
                $this->translator->trans('message.security.banned')
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}