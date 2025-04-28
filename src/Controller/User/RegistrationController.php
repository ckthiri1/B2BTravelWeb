<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $profileImageDirectory
    ) {}

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // If the form is submitted and valid, do a server-side email check as a fallback
        if ($form->isSubmitted() && $form->isValid()) {

            // Handle profile image upload
            $profileImageFile = $form->get('profileImage')->getData();
            if ($profileImageFile) {
                $originalFilename = pathinfo($profileImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$profileImageFile->guessExtension();

                $profileImageFile->move($this->profileImageDirectory, $newFilename);
                $user->setImageUrl('uploads/profile_images/'.$newFilename);
            }

            // Handle password hashing and other properties
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPwd($plainPassword);
            $user->setHash($passwordHasher->hashPassword($user, $plainPassword));
            $user->setRole('user');
            $user->setNbrVoyage(0);
            $user->setIsVerified(true);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Registration successful! You can now log in.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/check-email', name: 'app_check_email', methods: ['POST'])]
    public function checkEmail(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $email = trim($request->request->get('email'));
        $existingUser = $entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        return new JsonResponse(['exists' => (bool) $existingUser]);
    }
}
