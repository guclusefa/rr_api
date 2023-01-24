<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct
    (
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    public function checkOldPassword($user, $oldPassword): void
    {
       if (!$oldPassword || !$this->userPasswordHasher->isPasswordValid($user, $oldPassword)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'L\'ancien mot de passe est incorrect');
       }
    }

    public function checkSameEmail($user, $email): void
    {
        if ($user->getEmail() === $email) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'L\'email est identique à l\'ancien');
        }
    }
}