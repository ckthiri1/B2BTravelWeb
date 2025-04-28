<?php
namespace App\Controller\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;



class ForgotPasswordController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/forgot-password', name: 'forgot_password')]
    public function show(): Response
    {
        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/forgot-password/handle', name: 'forgot_password_handle', methods: ['POST'])]
    public function handle(Request $request): Response
    {
        $email = $request->request->get('email');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            // Generate reset token
            $token = Uuid::v4()->toRfc4122();
            $expiry = new \DateTime('+1 hour');

            // Update user entity
            $user->setResetToken($token);
            $user->setTokenExpiry($expiry);
            $this->entityManager->flush();

            // Send email
            $this->sendResetEmail($user, $token);
        }

        $this->addFlash('success', 'If an account exists, you will receive a password reset link.');
        return $this->redirectToRoute('app_login');
    }



// src/Controller/ForgotPasswordController.php
private function sendResetEmail(User $user, string $token): void
{
    try {
        $email = (new TemplatedEmail())
            ->from(new Address('ckthiri00@gmail.com', 'appSymfony'))
            ->to(new Address($user->getEmail(), $user->getPrenom()))
            ->subject('Password Reset Request')
            ->htmlTemplate('emails/password_reset.html.twig')
            ->context([
                'resetUrl' => $this->generateUrl('reset_password', 
                    ['token' => $token], 
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'expiry' => new \DateTime('+1 hour')
            ]);

        // Add email logging
        $this->logger->info('Attempting to send email to: '.$user->getEmail());
        $this->mailer->send($email);
        $this->logger->info('Email sent successfully to: '.$user->getEmail());

    } catch (TransportExceptionInterface $e) {
        $this->logger->error('Email send failed: '.$e->getMessage());
        $this->addFlash('error', 'Error sending email: '.$e->getMessage());
    }
  }
}