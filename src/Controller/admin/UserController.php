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
}