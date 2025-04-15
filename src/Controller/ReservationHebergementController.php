<?php

namespace App\Controller;

use App\Entity\ReservationHebergement;
use App\Form\ReservationHebergementType;
use App\Repository\ReservationHebergementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;


#[Route('/reservation/hebergement')]
final class ReservationHebergementController extends AbstractController
{
    #[Route('/export', name: 'app_reservation_hebergement_export', methods: ['GET'])]
    public function exportPdf(
        Request $request,
        ReservationHebergementRepository $reservationHebergementRepository
    ): Response {
    // Configuration de Dompdf
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new Dompdf($options);
    
    // Récupération des données
    $reservations = $reservationHebergementRepository->search(
        $request->query->get('status'),
        $request->query->get('type'),
        $request->query->get('sortPrice'),
        $request->query->get('sortDate'),
        $request->query->get('sortName')
    );

    // Génération du HTML
    $html = $this->renderView('reservation_hebergement/export.html.twig', [
        'reservations' => $reservations
    ]);

    // Conversion en PDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Envoi du PDF
    return new Response(
        $dompdf->output(), 
        Response::HTTP_OK,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="reservations.pdf"'
        ]
    );
}

    #[Route('/new', name: 'app_reservation_hebergement_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $reservationHebergement = new ReservationHebergement();
    $form = $this->createForm(ReservationHebergementType::class, $reservationHebergement);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Validation serveur du calcul
        $start = $reservationHebergement->getDate();
        $end = $reservationHebergement->getDateFin();
        $diff = $start->diff($end);
        $days = $diff->days; // +1 pour inclure le dernier jour
        
        $reservationHebergement->setPrix(
            $reservationHebergement->getHebergement()->getPrix() * $days
        );

        $entityManager->persist($reservationHebergement);
        $entityManager->flush();

        return $this->redirectToRoute('app_reservation_hebergement_index');
    }

    return $this->render('reservation_hebergement/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/{idResH}', name: 'app_reservation_hebergement_show', methods: ['GET'])]
    public function show(ReservationHebergement $reservationHebergement): Response
    {
        return $this->render('reservation_hebergement/show.html.twig', [
            'reservation_hebergement' => $reservationHebergement,
        ]);
    }

    #[Route('/{idResH}/edit', name: 'app_reservation_hebergement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReservationHebergement $reservationHebergement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationHebergementType::class, $reservationHebergement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Recalcul serveur du prix
            $start = $reservationHebergement->getDate();
            $end = $reservationHebergement->getDateFin();
            $diff = $start->diff($end);
            $days = $diff->days;
            
            if($reservationHebergement->getHebergement()) {
                $reservationHebergement->setPrix(
                    $reservationHebergement->getHebergement()->getPrix() * $days
                );
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_reservation_hebergement_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('reservation_hebergement/edit.html.twig', [
            'reservation_hebergement' => $reservationHebergement,
            'form' => $form,
        ]);
    }

    #[Route('/{idResH}', name: 'app_reservation_hebergement_delete', methods: ['POST'])]
    public function delete(Request $request, ReservationHebergement $reservationHebergement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservationHebergement->getIdResH(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservationHebergement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_hebergement_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route(name: 'app_reservation_hebergement_index', methods: ['GET'])]
    public function index(ReservationHebergementRepository $repository, Request $request): Response
    {
        $reservations = $repository->search(
            $request->query->get('status'),
            $request->query->get('type'),
            $request->query->get('search'),
            $request->query->get('sortPrice'),
            $request->query->get('sortDate'),
            $request->query->get('sortName')
        );
    
        return $this->render('reservation_hebergement/index.html.twig', [
            'reservation_hebergements' => $reservations,
        ]);
    }

}
