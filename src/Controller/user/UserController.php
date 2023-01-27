<?php

namespace App\Controller\user;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SerializerService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly UserRepository $userRepository,
        private readonly UserService $userService,
        private readonly TranslatorInterface $translator
    )
    {
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

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
        // check access
        $this->userService->checkAccess($user, $this->getUser());
        // get groups
        $groups = User::GROUP_ITEM;
        if ($this->userService->allowedConfidentialFields($user, $this->getUser())) {
            $groups = User::GROUP_ITEM_CONFIDENTIAL;
        }
        // get, serialize & return
        $user = $this->serializerService->serialize($groups, $user);
        return new JsonResponse(
            $this->serializerService->getSerializedData($user),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}', name: 'api_users_update', methods: ['PUT'])]
    public function update(User $user, Request $request): JsonResponse
    {
        // check access
        $this->userService->checkUpdateAccess($user, $this->getUser());
        // deserialize & update
        $updatedUser = $this->serializerService->deserialize(User::GROUP_UPDATE, $request, User::class);
        $this->userService->updateUser($user, $updatedUser);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.user.updated_success')],
            Response::HTTP_OK
        );
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/photo', name: 'api_users_update_photo', methods: ['POST'])]
    public function updatePhoto(User $user, Request $request): JsonResponse
    {
        // check access
        $this->userService->checkUpdateAccess($user, $this->getUser());
        // update
        $photo = $request->files->get('photo');
        $this->userService->updatePhoto($user, $photo);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.user.updated_photo_success')],
            Response::HTTP_OK
        );
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/password', name: 'api_users_update_password', methods: ['PUT'])]
    public function updatePassword(User $user, Request $request): JsonResponse
    {
        // check access
        $this->userService->checkUpdateAccess($user, $this->getUser());
        // deserialize & update
        $updatedUser = $this->serializerService->deserialize(User::GROUP_UPDATE_PASSWORD, $request, User::class);
        $oldPassword = json_decode($request->getContent())->old ?? null;
        $this->userService->updatePassword($user, $oldPassword, $updatedUser);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.user.updated_password_success')],
            Response::HTTP_OK
        );
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/email', name: 'api_users_update_email', methods: ['PUT'])]
    public function updateEmail(User $user, Request $request): JsonResponse
    {
        // check access
        $this->userService->checkUpdateAccess($user, $this->getUser());
        // deserialize & update
        $updatedUser = $this->serializerService->deserialize(User::GROUP_UPDATE_EMAIL, $request, User::class);
        $oldPassword = json_decode($request->getContent())->old ?? null;
        $this->userService->updateEmail($user, $oldPassword, $updatedUser->getEmail());
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.user.updated_email_success')],
            Response::HTTP_OK
        );
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}/delete', name: 'api_users_delete', methods: ['DELETE'])]
    public function delete(User $user): JsonResponse
    {
        // check access
        $this->userService->checkUpdateAccess($user, $this->getUser());
        // delete user
        $this->userRepository->remove($user, true);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.user.deleted_success')],
            Response::HTTP_OK
        );
    }
}