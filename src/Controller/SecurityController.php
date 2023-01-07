<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Service\MailerService;
use App\Service\SerializerService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): void
    {
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        // deserialize & validate
        $user = $this->serializerService->deserialize(User::GROUP_WRITE ,$request, User::class);
        $errors = $this->serializerService->validate($user);
        if ($errors) return new JsonResponse($errors, Response::HTTP_BAD_REQUEST, [], true);
        // save and persist
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        // serialize & return
        $user = $this->serializerService->serialize(User::GROUP_ITEM, $user);
        return new JsonResponse($user, Response::HTTP_CREATED, [], true);
    }

    /**
     * @throws Exception
     */
    #[Route('/confirm-email', name: 'api_confirm_email', methods: ['POST'])]
    public function confirmEmail(Request $request): JsonResponse
    {
        // email
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        if (!$email) throw new HttpException(Response::HTTP_BAD_REQUEST, 'Veuillez renseigner votre adresse email');
        // get user from email and check if user exists
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user && !$user->isIsVerified()){
            $payload = ['id' => $user->getId()];
            $token = $this->jwtService->generateToken($payload);
            $this->mailerService->sendEmail(
                $user->getEmail(),
                'Confirmation de votre adresse email',
                "confirmation",
                ['token' => $token, 'validity' => $this->jwtService->getValidityInHours($token)]
            );
        }
        // return
        return new JsonResponse(
            ['message' => 'Si un compte existe avec cette adresse email et qu\'il n\'a pas encore été confirmé, un email de confirmation vous a été envoyé'],
            Response::HTTP_OK
        );
    }

    #[Route('/verify-email/{token}', name: 'api_verify_email', methods: ['GET'])]
    public function verifyEmail(string $token): JsonResponse
    {
        // extract id from token
        if($this->jwtService->isValid($token) && !$this->jwtService->isExpired($token) && $this->jwtService->checkSignature($token)) {
            $payload = $this->jwtService->getPayload($token);
            $user = $this->entityManager->getRepository(User::class)->find($payload['id']);
            // check if user exists and is not confirmed
            if (!$user) throw new HttpException(Response::HTTP_NOT_FOUND, 'Utilisateur non trouvé');
            if ($user->isIsVerified()) throw new HttpException(Response::HTTP_BAD_REQUEST, 'Votre compte est déjà confirmé');
            // confirm user
            $user->setIsVerified(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            // return
            return new JsonResponse(
                ['message' => 'Votre adresse email a bien été vérifiée'],
                Response::HTTP_OK
            );
        } else {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Token de vérification invalide');
        }
    }

    #[Route('/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        // email
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        if (!$email) throw new HttpException(Response::HTTP_BAD_REQUEST, 'Veuillez renseigner votre adresse email');
        // get user from email and check if user exists
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user){
            $payload = ['id' => $user->getId()];
            $token = $this->jwtService->generateToken($payload);
            $this->mailerService->sendEmail(
                $user->getEmail(),
                'Réinitialisation de votre mot de passe',
                "forgot-password",
                ['token' => $token, 'validity' => $this->jwtService->getValidityInHours($token)]
            );
        }
        // return
        return new JsonResponse(
            ['message' => 'Si un compte existe avec cette adresse email, un email de réinitialisation vous a été envoyé'],
            Response::HTTP_OK
        );
    }

    #[Route('/reset-password/{token}', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request, String $token): JsonResponse
    {
        // extract id from token
        if($this->jwtService->isValid($token) && !$this->jwtService->isExpired($token) && $this->jwtService->checkSignature($token)) {
            // get user from id
            $payload = $this->jwtService->getPayload($token);
            $user = $this->entityManager->getRepository(User::class)->find($payload['id']);
            if (!$user) throw new HttpException(Response::HTTP_NOT_FOUND, 'Utilisateur non trouvé');
            // deserialize & validate
            $updatedPassword = $this->serializerService->deserialize(User::GROUP_UPDATE_PASSWORD, $request, User::class);
            $user->setPassword($updatedPassword->getPassword());
            $this->serializerService->validate($user);
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
            // save and persist
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            // return
            return new JsonResponse(
                ['message' => 'Votre mot de passe a bien été réinitialisé'],
                Response::HTTP_OK
            );
        } else {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Token de réinitialisation invalide');
        }
    }
}