<?php
// src/Service/PasswordResetService.php
namespace App\Service;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResetService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function sendPasswordResetEmail(User $user): void
    {
        $token = new PasswordResetToken($user);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'reset_url' => $this->urlGenerator->generate('reset_password', [
                    'token' => $token->getToken()
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                'expiration_date' => $token->getExpiresAt()
            ]);

        $this->mailer->send($email);
    }
}