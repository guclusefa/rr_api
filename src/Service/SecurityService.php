<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SecurityService
{
    public function __construct
    (
        private readonly JWTService $jwtService,
        private readonly MailerService $mailerService,
        private readonly EntityManagerInterface $entityManager
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

    public function sendTokenFromEmail(Request $request, $subject, $template): void
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
}