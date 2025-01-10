<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerManager
{

    const ADMIN_EMAIL = 'admin@loaberry.com';

    public function __construct(private MailerInterface $mailer)
    {

    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendUserVerifiedEmail(User $user, string $html): void {

        // Create and send the email
        $email = (new Email())
            ->from(self::ADMIN_EMAIL)
            ->to($user->getEmail())
            ->subject('Please verify email!')
            ->html($html);

        $this->mailer->send($email);
    }

}