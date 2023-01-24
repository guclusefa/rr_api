<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FileUploaderService;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly FileUploaderService $fileUploaderService,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    )
    {
    }

    private function isMe(User $user): bool
    {
        return $this->getUser() === $user;
    }

    private function isBanned(User $user): void
    {
        if ($user->isIsBanned()) throw new HttpException(Response::HTTP_FORBIDDEN, 'Cet utilisateur est banni');
    }

    private function checkAutho(User $user): void
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isMe($user)){
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                'Vous n\'avez pas les droits d\'acceder à cette ressource'
            );
        }
        if (!$this->isGranted('ROLE_ADMIN')){
            $this->isBanned($user);
        }
    }

    #[Route('', name: 'api_users', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // criterias
        $search = $request->query->get('search');
        $certified = $request->query->get('certified');
        // arrays of criterias
        $states = $request->query->all('state');
        $genders = $request->query->all("gender");
        // pagination
        $order = $request->query->get('order', 'id');
        $direction = $request->query->get('direction', 'ASC');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        // get, serialize & return
        $users = $this->userRepository->advanceSearch($this->getUser(), $search, $certified, $states, $genders, $order, $direction, $page, $limit);
        $users = $this->serializerService->serialize(User::GROUP_GET, $users);
        return new JsonResponse(
            $users,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/me', name: 'api_users_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        // get, serialize & return
        $user = $this->serializerService->serialize(User::GROUP_ITEM_CONFIDENTIAL, $this->getUser());
        return new JsonResponse(
            $this->serializerService->getSerializedData($user),
            Response::HTTP_OK,
            [],
            true
        );
    }


    #[Route('/{id}', name: 'api_users_show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        if ($this->isMe($user)) {
            $user = $this->serializerService->serialize(User::GROUP_ITEM_CONFIDENTIAL, $user);
        } else {
            $user = $this->serializerService->serialize(User::GROUP_ITEM, $user);
        }
        // return
        return new JsonResponse(
            $this->serializerService->getSerializedData($user),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'api_users_update', methods: ['PUT'])]
    public function update(User $user, Request $request): JsonResponse
    {
        // check autho
        $this->checkAutho($user);
        // check request & deserialize
        $stateRequest = json_decode($request->getContent())->state ?? null;
        if ($stateRequest && !is_int($stateRequest)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Le département doit être un nombre');
        }
        $updatedUser = $this->serializerService->deserialize(User::GROUP_UPDATE, $request, User::class);
        // update user
        $user->setUsername($updatedUser->getUsername());
        $user->setFirstName($updatedUser->getFirstName());
        $user->setLastName($updatedUser->getLastName());
        $user->setGender($updatedUser->getGender());
        $user->setBirthDate($updatedUser->getBirthDate());
        $user->setBio($updatedUser->getBio());
        $user->setState($updatedUser->getState());
        // check for errors
        $this->serializerService->checkErrors($user);
        // save and persist
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'Utilisateur modifié avec succès'],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/photo', name: 'api_users_update_photo', methods: ['POST'])]
    public function updatePhoto(User $user, Request $request): JsonResponse
    {
        // check autho
        $this->checkAutho($user);
        // check & upload file
        $photo = $request->files->get('photo');
        if ($photo) {
            // check and upload photo
            $user->setPhoto(
                $this->fileUploaderService->uploadPhoto(
                    $photo,
                    $user->getId(),
                    $this->getParameter("app.user.images.path")
                )
            );
        } else {
            // delete file from server if exists
            $photoName = $user->getPhoto();
            if ($photoName) {
                $photoPath = $this->getParameter("app.user.images.path") . '/' . $photoName;
                $this->fileUploaderService->deleteFile($photoPath);
            }
            $user->setPhoto(null);
        }
        // persist & flush
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'Photo modifiée avec succès'],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/password', name: 'api_users_update_password', methods: ['PUT'])]
    public function updatePassword(User $user, Request $request): JsonResponse
    {
        // check autho
        $this->checkAutho($user);
        // check if old password is correct
        $oldPassword = json_decode($request->getContent())->old?? null;
        if (!$oldPassword || !$this->userPasswordHasher->isPasswordValid($user, $oldPassword)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'L\'ancien mot de passe est incorrect');
        }
        // deserialize & update
        $updatedUser = $this->serializerService->deserialize(User::GROUP_UPDATE_PASSWORD, $request, User::class);
        $user->setPassword($updatedUser->getPassword());
        // check for errors
        $this->serializerService->checkErrors($user);
        // save and persist
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'Mot de passe modifié avec succès'],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/email', name: 'api_users_update_email', methods: ['PUT'])]
    public function updateEmail(User $user, Request $request): JsonResponse
    {
        // check autho
        $this->checkAutho($user);
        // check if old password is correct
        $oldPassword = json_decode($request->getContent())->old?? null;
        if (!$oldPassword || !$this->userPasswordHasher->isPasswordValid($user, $oldPassword)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'L\'ancien mot de passe est incorrect');
        }
        // deserialize & update
        $updatedUser = $this->serializerService->deserialize(User::GROUP_UPDATE_EMAIL, $request, User::class);
        if ($user->getEmail() === $updatedUser->getEmail()) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Vous êtes déjà inscrit avec cette adresse email');
        }
        $user->setEmail($updatedUser->getEmail());
        $user->setIsVerified(false);
        // check for errors
        $this->serializerService->checkErrors($user);
        // save and persist
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'Adresse email modifiée avec succès, veuillez vous reconnecter'],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/delete', name: 'api_users_delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        // check autho
        $this->checkAutho($user);
        // delete user
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'Utilisateur supprimé avec succès'],
            Response::HTTP_OK
        );
    }
}