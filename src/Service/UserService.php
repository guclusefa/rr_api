<?php

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct
    (
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserRepository $userRepository,
    )
    {
    }

    public function checkAccess($user, $me): void
    {
        if (!$this->userRepository->isAccesibleToMe($user, $me)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Vous n\'avez pas accès à cet utilisateur');
        }
    }

    public function checkUpdateAccess($user, $me): void
    {
        if ($user !== $me) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Vous n\'avez pas accès à la modification de cet utilisateur');
        }
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