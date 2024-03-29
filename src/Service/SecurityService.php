<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityService
{
    public function __construct
    (
        private readonly JWTService $jwtService,
        private readonly MailerService $mailerService,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly ParameterBagInterface $params
    )
    {
    }

    private function checkEmail(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        if (!$email) throw new HttpException(
            Response::HTTP_BAD_REQUEST,
            $this->translator->trans('message.security.email.missing_error')
        );
        return $email;
    }

    private function sendMail(User $user, $subject, $template): void
    {
        $payload = ['id' => $user->getId()];
        $token = $this->jwtService->generateToken($payload);
        $url = $this->params->get('app.webapp.url');
        $link = "/profile/verify/" . $token;
        if ($template == "forgot-password") {
            $link = '/forgot-password/' . $token;
        }
        try {
            $this->mailerService->sendEmail(
                $user->getEmail(),
                $subject,
                $template,
                [
                    'title' => $subject,
                    'token' => $token,
                    'link' => $this->translator->trans('message.security.email.link', ["%link%" => $url . $link]),
                    'validity' => $this->translator->trans('message.security.email.link_validity', ["%validity%" => $this->jwtService->getValidityInHours($token)]),
                    'ignore_or_report' => $this->translator->trans('message.security.email.ignore_or_report')
                ]
            );
        } catch (\Exception $e) {
            throw new HttpException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $this->translator->trans('message.security.email.send_error')
            );
        }
    }

    public function sendTokenFromEmail(Request $request, $subject, $template): void
    {
        // check if email is provided
        $email = $this->checkEmail($request);
        // get user from email and check if user exists
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user) {
            $this->sendMail($user, $subject, $template);
        }
    }

    public function sendTokenFromUser(User $user, $subject, $template): void
    {
        if ($template == "confirmation" && $user->isIsVerified()){
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $this->translator->trans('message.security.confirmation_error')
            );
        }
        $this->sendMail($user, $subject, $template);
    }
}