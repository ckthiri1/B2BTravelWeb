<?php

namespace App\Controller;

use App\Entity\Reservation_voyage;
use App\Entity\Vol;
use App\Repository\VolRepository;
use App\Service\UserSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/Reservation')]
final class ReservationVolController extends AbstractController
{
    #[Route('/Page', name: 'reservation_page')]
    public function index(VolRepository $volRepo, )
    {

        return $this->render('Interface User/ReservationVol.twig', );
    }

    
}
