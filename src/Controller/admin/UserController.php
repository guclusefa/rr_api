<?php

namespace App\Controller\admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SerializerService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/admin/users')]
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

    #[Route('/{id}/certify', name: 'api_admin_users_certify', methods: ['POST'])]
    public function certify(User $user): JsonResponse
    {
        // certify
        $isCertified = $user->isIsCertified();
        $user->setIsCertified(!$isCertified);
        if ($isCertified) {
            $message = $this->translator->trans('message.user.uncertify_success');
        } else {
            $message = $this->translator->trans('message.user.certify_success');
        }
        $this->userRepository->save($user, true);
        // return
        return new JsonResponse(
            ['message' => $message],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/ban', name: 'api_admin_users_ban', methods: ['POST'])]
    public function ban(User $user): JsonResponse
    {
        // ban
        $isBanned = $user->isIsBanned();
        $user->setIsBanned(!$isBanned);
        if ($isBanned) {
            $message = $this->translator->trans('message.user.unban_success');
        } else {
            $message = $this->translator->trans('message.user.ban_success');
        }
        $this->userRepository->save($user, true);
        // return
        return new JsonResponse(
            ['message' => $message],
            Response::HTTP_OK
        );
    }

    #[Route('/{id}/upgrade/{role}', name: 'api_admin_users_upgrade', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function upgrade(User $user, $role): JsonResponse
    {
        // valid role
        $role = match ($role) {
            '1' => ['ROLE_MODERATOR'],
            '2' => ['ROLE_ADMIN'],
            '3' => ['ROLE_SUPER_ADMIN'],
            default => [],
        };
        // upgrade
        $user->setRoles($role);
        // save
        $this->userRepository->save($user, true);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.user.upgrade_success')],
            Response::HTTP_OK
        );
    }
}