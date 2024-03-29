<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class JWTService
{
    private array $header;
    private string $secret;
    public function __construct
    (
        private readonly ParameterBagInterface $params,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator
    )
    {
        $this->header = ["alg" => "HS256", "typ" => "JWT", "cty" => "JWT"];
        $this->secret = $this->params->get('app.jwtsecret');
    }

    // generate a JWT token
    public function generateToken(array $payload, int $validity = 3600): string
    {
        if ($validity > 0) {
            $now = new \DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }
        // encode the header
        $base64Header = base64_encode(json_encode($this->header));
        $base64Payload = base64_encode(json_encode($payload));
        // clear the signature
        $base64Header = $this->cleanToken($base64Header);
        $base64Payload = $this->cleanToken($base64Payload);
        // generate the signature
        $secret = base64_decode($this->secret);
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = base64_encode($signature);
        $base64Signature = $this->cleanToken($base64Signature);
        // generate the token
        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    // check signature of a JWT token
    public function checkSignature(string $token): bool
    {
        $payload = $this->getPayload($token);
        $signature = $this->generateToken($payload, 0);
        return $signature === $token;
    }

    // get header
    public function getHeader(string $token): array
    {
        $header = explode('.', $token)[0];
        $header = base64_decode($header);
        return json_decode($header, true);
    }

    // get payload of a JWT token
    public function getPayload(string $token): array
    {
        $payload = explode('.', $token)[1];
        $payload = base64_decode($payload);
        return json_decode($payload, true);
    }

    // get validity of a JWT token
    public function getValidity(string $token): int
    {
        $payload = $this->getPayload($token);
        if (isset($payload['exp'])) {
            $now = new \DateTimeImmutable();
            $exp = $payload['exp'];
            $now = $now->getTimestamp();
            return $exp - $now;
        }
        return 0;
    }

    // get validity of a JWT token in hours
    public function getValidityInHours(string $token): float
    {
        $validity = $this->getValidity($token);
        return $validity / 3600;
    }

    // verify a JWT token
    public function isValid(string $token): bool
    {
        return preg_match(
                '/^[a-zA-Z0-9_-]+.[a-zA-Z0-9_-]+.[a-zA-Z0-9_-]+$/',
                $token
            ) === 1;
    }

    // check if a JWT token is expired
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);
        $exp = $payload['exp'];
        $now = new \DateTimeImmutable();
        return $now->getTimestamp() > $exp;
    }

    // clean
    private function cleanToken(string $base64): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], $base64);
    }

    // check if a JWT token is valid
    public function checkToken(string $token): bool
    {
        if (!($this->isValid($token) && $this->checkSignature($token) && !$this->isExpired($token))) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                $this->translator->trans('message.jwt.invalid_token')
            );
        }
        return true;
    }

    // get user from a JWT token
    public function getUserFromToken(string $token)
    {
        // check if token is valid
        $this->checkToken($token);
        // get user from token
        $payload = $this->getPayload($token);
        $user = $this->entityManager->getRepository(User::class)->find($payload['id']);
        // check if user exists
        if (!$user) throw new HttpException(
            Response::HTTP_BAD_REQUEST,
            $this->translator->trans('message.jwt.invalid_user')
        );
        return $user;
    }
}