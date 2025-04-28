<?php
// src/Controller/DashboardController.php
namespace App\Controller\User;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function adminDashboard(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('dashboard/admin.html.twig', [
            'total_users' => $userRepository->countAllUsers(),
        ]);
    }

    #[Route('/user/count', name: 'user_count', methods: ['GET'])]
    public function getUserCount(UserRepository $userRepository): JsonResponse
    {
        $totalUsers = $userRepository->countAllUsers();
        $lastMonthCount = $userRepository->countUsersLastMonth();
        $bannedUsers = $userRepository->countBannedUsers();
        $unbannedUsers = $userRepository->countUnbannedUsers();
        
        $trend = 0;
        if ($lastMonthCount > 0) {
            $trend = round((($totalUsers - $lastMonthCount) / $lastMonthCount) * 100);
        }
        
        return $this->json([
            'userCount' => $totalUsers,
            'trend' => $trend,
            'bannedUsers' => $bannedUsers,
            'unbannedUsers' => $unbannedUsers
        ]);
    }
}