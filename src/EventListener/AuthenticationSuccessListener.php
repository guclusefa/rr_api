<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\SerializerService;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener
{
    private $jwtEncoder;
    private $userRepository;
    private $serializerService;

    public function __construct(JWTEncoderInterface $jwtEncoder, UserRepository $userRepository, SerializerService $serializerService)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->userRepository = $userRepository;
        $this->serializerService = $serializerService;
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $token = $event->getData()['token'];
        $payload = $this->jwtEncoder->decode($token);
        $user = $this->userRepository->findOneBy(['id' => $payload['id']]);
        $user = $this->serializerService->serialize(User::GROUP_ITEM_CONFIDENTIAL, $user);


        $expirationDate = new \DateTime();
        $expirationDate->setTimestamp($payload['exp']);

        $event->setData([
            'token' => $token,
            'expirationDate' => $expirationDate->format("Y-m-d H:i:s"),
            'data' => json_decode($user),
        ]);
    }
}