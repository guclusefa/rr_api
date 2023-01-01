<?php

namespace App\Service;

use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
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
    public function sendEmail(string $to, string $subject, string $template, array $context): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->sender, $this->name))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($context);
        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new Exception($e->getMessage());
        }
    }
}