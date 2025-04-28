<?php
// src/Controller/ProfileController.php
namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $profileImageDirectory
    ) {}

    public function index(Security $security): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        // If not logged in, redirect to login
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/{userId}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(UserType::class, $user, ['is_new' => false]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $profileImageFile = $form->get('profileImage')->getData();
            if ($profileImageFile) {
                $originalFilename = pathinfo($profileImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$profileImageFile->guessExtension();
                
                $profileImageFile->move(
                    $this->profileImageDirectory,
                    $newFilename
                );
                $user->setImageUrl('uploads/profile_images/'.$newFilename);
            }

            // Handle password if provided
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPwd($plainPassword);
                $user->setHash(
                    $passwordHasher->hashPassword($user, $plainPassword)
                );
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully');
            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/{userId}', name: 'profile')]
    public function profile(User $user): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}