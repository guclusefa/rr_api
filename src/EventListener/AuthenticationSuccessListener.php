<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SerializerService;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationSuccessListener
{
    private $jwtEncoder;
    private $userRepository;
    private $serializerService;

    public function __construct(JWTEncoderInterface $jwtEncoder, UserRepository $userRepository, SerializerService $serializerService, private readonly TranslatorInterface $translator,)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->userRepository = $userRepository;
        $this->serializerService = $serializerService;
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $token = $event->getData()['token'];
        $payload = $this->jwtEncoder->decode($token);
        // User
        $user = $this->userRepository->findOneBy(['id' => $payload['id']]);
        if ($user !== null && $this->userRepository->isBanned($user)) {
            // get ban reason and end date
            $ban = $this->userRepository->getMostCurrentBan($user);
            $reason = $ban->getReason();
            $endDate = $ban->getEndDate();
            // format date
            $formatter = new \IntlDateFormatter($this->translator->trans('values.locale'), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL);
            $formatter->setPattern($this->translator->trans('values.date.datetime_format'));
            $endDate = $formatter->format($endDate);
            // throw exception with the corresponding message
            if ($endDate == null) {
                $message = 'message.security.banned_perm';
            } else {
                $message = 'message.security.banned';
            }
            throw new HttpException(
                Response::HTTP_FORBIDDEN,
                $this->translator->trans($message, ['%date%' => $endDate, '%reason%' => $reason])
            );
        }
        $user = $this->serializerService->serialize(User::GROUP_ITEM_CONFIDENTIAL, $user);
        // get the token expiration date
        $expirationDate = new \DateTime();
        $expirationDate->setTimestamp($payload['exp']);
        $event->setData([
            'token' => $token,
            'expirationDate' => $expirationDate->format("Y-m-d H:i:s"),
            'data' => json_decode($user),
        ]);
    }
}