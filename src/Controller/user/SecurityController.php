<?php

namespace App\Controller\user;

use App\Entity\User;
use App\Service\JWTService;
use App\Service\SecurityService;
use App\Service\SerializerService;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api')]
class SecurityController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly JWTService $jwtService,
        private readonly UserService $userService,
        private readonly SecurityService $securityService,
        private readonly TranslatorInterface $translator
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
        $this->securityService->sendTokenFromEmail($request,
            $this->translator->trans('message.security.email.welcome_subject', ["%site_name%" => $this->getParameter("app.site_name")]),
            'welcome'
        );
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.security.register_success')],
            Response::HTTP_CREATED
        );
    }

    #[Route('/check-token', name: 'api_check_token', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function checkToken(Request $request, JWTEncoderInterface $jwtEncoder): JsonResponse
    {
        // get token
        $token = $request->headers->get('Authorization');
        $token = str_replace('Bearer ', '', $token);
        // get payload
        $payload = $jwtEncoder->decode($token);
        // get expiration date
        $expirationDate = new \DateTime();
        $expirationDate->setTimestamp($payload['exp']);
         // return
        return new JsonResponse(
            [
                "token" => $token,
                "payload" => $payload,
                "expirationDate" => $expirationDate->format("Y-m-d H:i:s")
            ],
            Response::HTTP_OK
        );
    }

    #[Route('/refresh-token', name: 'api_refresh_token', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function refreshToken(Request $request, JWTTokenManagerInterface $jwtManager, JWTEncoderInterface $jwtEncoder): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof UserInterface) {
            // to please the ide but this should never happen
            throw new \LogicException(
                $this->translator->trans('message.jwt.invalid_user')
            );
        }
        // get new token
        $newToken = $jwtManager->create($currentUser);
        $payload = $jwtEncoder->decode($newToken);
        // encode new token
        $newToken = $jwtEncoder->encode($payload);
        // get expiration date
        $expirationDate = new \DateTime();
        $expirationDate->setTimestamp($payload['exp']);
        return new JsonResponse([
            'token' => $newToken,
            "expirationDate" => $expirationDate->format("Y-m-d H:i:s")
        ]);
    }

    #[Route('/confirm-email', name: 'api_confirm_email', methods: ['POST'])]
    public function confirmEmail(Request $request): JsonResponse
    {
        // send token if email is valid
        $this->securityService->sendTokenFromEmail(
            $request,
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

    #[Route('/verify-email/{token}', name: 'api_verify_email', methods: ['PUT'])]
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

    #[Route('/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        // send token if email is valid
        $this->securityService->sendTokenFromEmail(
            $request,
            $this->translator->trans('message.security.email.reset_password_subject',
                ["%site_name%" => $this->getParameter("app.site_name")]),
            'forgot-password'
        );
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.security.reset_password_send')],
            Response::HTTP_OK
        );
    }

    #[Route('/reset-password/{token}', name: 'api_reset_password', methods: ['PUT'])]
    public function resetPassword(Request $request, String $token): JsonResponse
    {
        // check token and get user
        $user = $this->jwtService->getUserFromToken($token);
        // deserialize & update
        $updatedPassword = $this->serializerService->deserialize(User::GROUP_UPDATE_PASSWORD, $request, User::class);
        $this->userService->resetPassword($user, $updatedPassword);
        // return
        return new JsonResponse(
            ['message' => $this->translator->trans('message.security.reset_password_success')],
            Response::HTTP_OK
        );
    }
}