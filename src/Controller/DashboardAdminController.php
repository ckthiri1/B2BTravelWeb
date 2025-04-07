<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAdminController extends AbstractController
{
    #[Route('/Dashboard', name: 'Dashboard')]
    public function index(): Response
    {
        return $this->render('DashboardAdmin.twig', [
            'userCount' => 250,
            'voyageCount' => 0,
            'rankCount' => 0,
            'reservationCount' => 500,
            'eventCount' => 30,
            'complaintCount' => 15
        ]);
    }
}
