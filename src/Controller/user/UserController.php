<?php

namespace App\Controller\user;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\JWTService;
use App\Service\SecurityService;
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
        private readonly TranslatorInterface $translator,
        private readonly SecurityService $securityService,
        private readonly JWTService $jwtService
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
        $roles = $request->query->all('role');
        $states = $request->query->all('state');
        $genders = $request->query->all("gender");
        // pagination
        $order = $request->query->get('order', 'id');
        $direction = $request->query->get('direction', 'ASC');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        // get, serialize & return
        $users = $this->userRepository->advanceSearch($search, $certified, $roles, $states, $genders, $order, $direction, $page, $limit);
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
        $this->userService->checkAccess($user);
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
        $this->userService->updateEmail($user, $oldPassword, $updatedUser);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.user.updated_email_success')],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/confirm-email', name: 'api_users_confirm_email', methods: ['PUT'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function confirmEmail(User $user): JsonResponse
    {
        // send token if email is valid
        $this->securityService->sendTokenFromUser(
            $user,
            $this->translator->trans('message.security.email.confirm_subject',
                ["%site_name%" => $this->getParameter("app.site_name")]),
            'confirmation'
        );
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.security.confirmation_send')],
            Response::HTTP_OK
        );
    }

    #[Route('/verify-email/{token}', name: 'api_users_verify_email', methods: ['PUT'])]
    public function verifyEmail(string $token): JsonResponse
    {
        // check token and get user
        $user = $this->jwtService->getUserFromToken($token);
        // verify user
        $this->userService->verifyEmail($user);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.security.confirmation_success')],
            Response::HTTP_OK
        );
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/{id}', name: 'api_users_delete', methods: ['DELETE'])]
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