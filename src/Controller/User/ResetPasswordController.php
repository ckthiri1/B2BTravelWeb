<?php
// src/Controller/ResetPasswordController.php
namespace App\Controller\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function reset(string $token, Request $request): Response
    {
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['reset_token' => $token]);

        if (!$user || $user->getTokenExpiry() < new \DateTime()) {
            $this->addFlash('error', 'Invalid or expired reset token.');
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');
            
            // Update password
            $user->setHash(
                $this->passwordHasher->hashPassword($user, $newPassword)
            );
            
            // Clear reset token
            $user->setResetToken(null);
            $user->setTokenExpiry(null);
            $user->setPwd($newPassword);
            
            $this->entityManager->flush();

            $this->addFlash('success', 'Password updated successfully!');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig');
    }
}