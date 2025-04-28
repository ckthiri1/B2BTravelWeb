<?php

namespace App\Controller\User\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use App\Entity\User;
use App\Form\AdminUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AdminProfileController extends AbstractController
{
    public function index(Security $security): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        // If not logged in, redirect to login
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('admin/user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    public function __construct(
        private SluggerInterface $slugger,
        private string $profileImageDirectory
    ) {}
    
    #[Route('/admin/user/{userId}/edit', name: 'admin_profile_edit')]
    public function edit(
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(AdminUserType::class, $user, ['is_new' => false]);
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
            return $this->redirectToRoute('admin_profile', ['userId' => $user->getUserId()]);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin_profile/{userId}', name: 'admin_profile')]
    public function profile(User $user): Response
    {
        return $this->render('admin/user/profile.html.twig', [
            'user' => $user,
        ]);
    }
}