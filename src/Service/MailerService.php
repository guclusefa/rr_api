<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailerService
{
    private string $sender;
    private string $name;
    public function __construct
    (
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $params,
    )
    {
        $this->sender = $this->params->get('app.mailer.sender');
        $this->name = $this->params->get('app.mailer.name');
    }

    /**
     * @throws Exception
     */
    public function sendEmail(string $to, string $subject, string $body): void
    {
        $email = (new Email())
            ->from(new Address($this->sender, $this->name))
            ->to($to)
            ->subject($subject)
            ->html($body)
            ->text($body);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new Exception($e->getMessage());
        }
    }
}