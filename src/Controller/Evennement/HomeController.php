<?php

namespace App\Controller\Evennement;

use App\Repository\EvennementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'EvennementHome')]
    public function index(
        EvennementRepository $evennementRepo,
       
    ): Response {
        $nextEvent = $evennementRepo->findNextEvent();
        $totalEvents = $evennementRepo->count([]);
        $topOrganisateur = $evennementRepo->findTopOrganisateur();
        
        return $this->render('home/index.html.twig', [
            'totalEvents' => $totalEvents,
            'topOrganisateur' => $topOrganisateur,
            'nextEvent' => $nextEvent,

        ]);
        
    }
    
}
