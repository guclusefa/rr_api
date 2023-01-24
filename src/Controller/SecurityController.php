<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Service\SecurityService;
use App\Service\SerializerService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SecurityController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly JWTService $jwtService,
        private readonly UserService $userService,
        private readonly SecurityService $securityService,
    )
    {
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        // deserialize & create
        $user = $this->serializerService->deserialize(User::GROUP_WRITE ,$request, User::class);
        $this->userService->createUser($user);
        // send mail
        $this->securityService->sendTokenFromEmail($request, 'Bienvenue', 'welcome');
        // return
        return new JsonResponse(
            ['message' => 'Votre compte a bien été créé, un email de confirmation vous a été envoyé'],
            Response::HTTP_CREATED
        );
    }

    #[Route('/confirm-email', name: 'api_confirm_email', methods: ['POST'])]
    public function confirmEmail(Request $request): JsonResponse
    {
        // send token if email is valid
        $this->securityService->sendTokenFromEmail($request, 'Confirmation de votre compte', 'confirmation');
        // return
        return new JsonResponse(
            ['message' => 'Si un compte existe avec cette adresse email et qu\'il n\'a pas encore été confirmé, un email de confirmation vous a été envoyé'],
            Response::HTTP_OK
        );
    }

    #[Route('/verify-email/{token}', name: 'api_verify_email', methods: ['GET'])]
    public function verifyEmail(string $token): JsonResponse
    {
        // check token and get user
        $user = $this->jwtService->getUserFromToken($token);
        // verify user
        $this->userService->verifyEmail($user);
        // return
        return new JsonResponse(
            ['message' => 'Votre adresse email a bien été vérifiée'],
            Response::HTTP_OK
        );
    }

    #[Route('/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        // send token if email is valid
        $this->securityService->sendTokenFromEmail($request, 'Réinitialisation de votre mot de passe', 'forgot-password');
        // return
        return new JsonResponse(
            ['message' => 'Si un compte existe avec cette adresse email, un email de réinitialisation vous a été envoyé'],
            Response::HTTP_OK
        );
    }

    #[Route('/reset-password/{token}', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request, String $token): JsonResponse
    {
        // check token and get user
        $user = $this->jwtService->getUserFromToken($token);
        // deserialize & update
        $updatedPassword = $this->serializerService->deserialize(User::GROUP_UPDATE_PASSWORD, $request, User::class);
        $this->userService->resetPassword($user, $updatedPassword);
        // return
        return new JsonResponse(
            ['message' => 'Votre mot de passe a bien été réinitialisé'],
            Response::HTTP_OK
        );
    }
}