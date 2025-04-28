<?php
namespace App\Controller\User\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin/user')]
class UserAdminController extends AbstractController
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $profileImageDirectory,
        private UserRepository $userRepository,
        private PaginatorInterface $paginator
    ) {}

    #[Route('/', name: 'admin_user_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $query = $this->userRepository->createQueryBuilder('u')
            ->orderBy('u.userId', 'DESC')
            ->getQuery();

        $users = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user, ['is_new' => true]);
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
    
            // Handle passwords
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPwd($plainPassword);
            $user->setHash(
                $passwordHasher->hashPassword($user, $plainPassword)
            );
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            $this->addFlash('success', 'User created successfully');
            return $this->redirectToRoute('admin_user_index');
        }
    
        return $this->render('admin/user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{userId}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
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
            $this->addFlash('success', 'User updated successfully');
            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    

    #[Route('/{userId}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        if ($this->isCsrfTokenValid('delete'.$user->getUserId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'User deleted successfully');
        }

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/search', name: 'admin_user_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $query = $request->query->get('q', '');
        if (empty($query)) {
            return $this->redirectToRoute('admin_user_index');
        }
    
        $users = $this->userRepository->search($query);
        
        return $this->render('admin/user/_user_rows.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{userId}/ban', name: 'admin_user_ban', methods: ['POST'])]
public function ban(
    Request $request,
    User $user,
    EntityManagerInterface $entityManager
): Response {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
    if ($this->isCsrfTokenValid('ban'.$user->getUserId(), $request->request->get('_token'))) {
        $user->setIsBanned(true);
        $user->setBannedUntil(new \DateTime('+1 week')); // 1 week ban
        $entityManager->flush();
        $this->addFlash('success', 'User banned successfully');
    }

    return $this->redirectToRoute('admin_user_index');
}

#[Route('/{userId}/unban', name: 'admin_user_unban', methods: ['POST'])]
public function unban(
    Request $request,
    User $user,
    EntityManagerInterface $entityManager
): Response {
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    if ($this->isCsrfTokenValid('unban'.$user->getUserId(), $request->request->get('_token'))) {
        $user->setIsBanned(false);
        $user->setBannedUntil(null);
        $user->setBannedReason(null);
        $entityManager->flush();
        $this->addFlash('success', 'User unbanned successfully');
    }

    return $this->redirectToRoute('admin_user_index');
}
}