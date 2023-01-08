<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Service\MailerService;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class SecurityController extends AbstractController
{
    public function __construct
    (
        private readonly SerializerService $serializerService,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly JWTService $jwtService,
        private readonly MailerService $mailerService
    )
    {
    }

    private function checkEmail(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        if (!$email) throw new HttpException(Response::HTTP_BAD_REQUEST, 'Veuillez renseigner votre adresse email');
        return $email;
    }

    private function sendMail(User $user, $subject, $template): void
    {
        $payload = ['id' => $user->getId()];
        $token = $this->jwtService->generateToken($payload);
        try {
            $this->mailerService->sendEmail(
                $user->getEmail(),
                $subject,
                $template,
                ['token' => $token, 'validity' => $this->jwtService->getValidityInHours($token)]
            );
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Une erreur est survenue lors de l\'envoi du mail');
        }
    }

    private function sendTokenFromEmail(Request $request, $subject, $template)
    {
        // check if email is provided
        $email = $this->checkEmail($request);
        // get user from email and check if user exists
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user) {
            if ($template == "confirmation" && $user->isIsVerified()){
                return;
            }
            $this->sendMail($user, $subject, $template);
        }
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        // deserialize
        $user = $this->serializerService->deserialize(User::GROUP_WRITE ,$request, User::class);
        // check for errors
        $this->serializerService->checkErrors($user);
        // save and persist
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // send mail
        $this->sendTokenFromEmail($request, 'Bienvenue', 'welcome');
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
        $this->sendTokenFromEmail($request, 'Confirmation de votre compte', 'confirmation');
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
        $this->jwtService->checkToken($token);
        $user = $this->jwtService->getUserFromToken($token);
        // check user
        if ($user->isIsVerified()) throw new HttpException(Response::HTTP_BAD_REQUEST, 'Votre adresse email est déjà vérifiée');
        // confirm user
        $user->setIsVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
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
        $this->sendTokenFromEmail($request, 'Réinitialisation de votre mot de passe', 'forgot-password');
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
        $this->jwtService->checkToken($token);
        $user = $this->jwtService->getUserFromToken($token);
        // deserialize & update
        $updatedPassword = $this->serializerService->deserialize(User::GROUP_UPDATE_PASSWORD, $request, User::class);
        $user->setPassword($updatedPassword->getPassword());
        // check for errors
        $this->serializerService->checkErrors($user);
        // save and persist
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // return
        return new JsonResponse(
            ['message' => 'Votre mot de passe a bien été réinitialisé'],
            Response::HTTP_OK
        );
    }
}