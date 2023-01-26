<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserService
{
    public function __construct
    (
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserRepository $userRepository,
        private readonly FileUploaderService $fileUploaderService,
        private readonly ParameterBagInterface $params,
        private readonly SerializerService $serializerService,
        private readonly TranslatorInterface $translator
    )
    {
    }

    public function checkAccess($user, $me): void
    {
        if (!$this->userRepository->isAccesibleToMe($user, $me)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, $this->translator->trans('message.user.access_denied'));
        }
    }

    public function checkUpdateAccess($user, $me): void
    {
        if ($user !== $me) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Vous n\'avez pas accès à la modification de cet utilisateur');
        }
    }

    public function allowedConfidentialFields($user, $me): bool
    {
        if ($user === $me) {
            return true;
        }
        return false;
    }

    public function formatUser($user, $baseUrl) : User
    {
        if ($user->getPhoto() !== null) {
            $user->setPhoto($baseUrl . "/" . $this->params->get("app.user.images.path") . $user->getPhoto());
        }
        return $user;
    }

    public function formatsUsers($users, $baseUrl): array
    {
        foreach ($users["data"] as $user) {
            $this->formatUser($user, $baseUrl);
        }
        return $users;
    }

    public function createUser($user): void
    {
        // check for errors
        $this->serializerService->checkErrors($user);
        // hash password
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        // save
        $this->userRepository->save($user, true);
    }

    public function updateUser($user, $updatedUser): void
    {
        // update user
        $user->setUsername($updatedUser->getUsername());
        $user->setFirstName($updatedUser->getFirstName());
        $user->setLastName($updatedUser->getLastName());
        $user->setGender($updatedUser->getGender());
        $user->setBirthDate($updatedUser->getBirthDate());
        $user->setBio($updatedUser->getBio());
        $user->setState($updatedUser->getState());
        $user->setIsActive($updatedUser->isIsActive());
        // check for errors
        $this->serializerService->checkErrors($user);
        // save
        $this->userRepository->save($user, true);
    }

    public function updatePhoto($user, $photo): void
    {
        if ($photo) {
            // check and upload photo
            $user->setPhoto(
                $this->fileUploaderService->uploadPhoto(
                    $photo,
                    $user->getId(),
                    $this->params->get("app.user.images.path")
                )
            );
        } else {
            // delete file from server if exists
            $photoName = $user->getPhoto();
            if ($photoName) {
                $photoPath = $this->params->get("app.user.images.path") . '/' . $photoName;
                $this->fileUploaderService->deleteFile($photoPath);
            }
            $user->setPhoto(null);
        }
        // save
        $this->userRepository->save($user, true);
    }

    private function checkOldPassword($user, $oldPassword): void
    {
       if (!$oldPassword || !$this->userPasswordHasher->isPasswordValid($user, $oldPassword)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'L\'ancien mot de passe est incorrect');
       }
    }

    public function resetPassword($user, $updatedUser): void
    {
        // set plain password
        $user->setPassword($updatedUser->getPassword());
        // check for errors
        $this->serializerService->checkErrors($user);
        // update
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $this->userRepository->save($user, true);
    }

    public function updatePassword($user, $oldPassword, $updatedUser): void
    {
        // check password
        $this->checkOldPassword($user, $oldPassword);
        // set plain password
        $user->setPassword($updatedUser->getPassword());
        // check for errors
        $this->serializerService->checkErrors($user);
        // update
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $this->userRepository->save($user, true);
    }

    private function checkSameEmail($user, $email): void
    {
        if ($user->getEmail() === $email) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'L\'email est identique à l\'ancien');
        }
    }

    public function updateEmail($user, $oldPassword, $updatedUser): void
    {
        // check password
        $this->checkOldPassword($user, $oldPassword);
        // check email
        $this->checkSameEmail($user, $updatedUser->getEmail());
        // update email and set isVerified to false
        $user->setEmail($updatedUser->getEmail());
        $user->setIsVerified(false);
        // check for errors
        $this->serializerService->checkErrors($user);
        // save user
        $this->userRepository->save($user, true);
    }

    public function verifyEmail($user): void
    {
        // check if user is already verified
        if ($user->isIsVerified()) throw new HttpException(Response::HTTP_BAD_REQUEST, 'Votre adresse email est déjà vérifiée');
        // confirm user
        $user->setIsVerified(true);
        // save
        $this->userRepository->save($user, true);
    }
}