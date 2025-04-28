<?php

namespace App\Controller\Hebergement;

use App\Entity\Hebergement;
use App\Entity\Reservation_voyage;
use App\Entity\ReservationHebergement;
use App\Form\ReservationHebergementType;
use App\Repository\HebergementRepository;
use App\Repository\Reservation_voyageRepository;
use App\Repository\ReservationHebergementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;
use Dompdf\Options;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;


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
        $form = $this->createForm(ReservationHebergementType::class, $reservationHebergement, [
            'hebergements' => $entityManager->getRepository(Hebergement::class)->findAll()
        ]);
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

    #[Route('/new_hebergement', name: 'new_hebergement', methods: ['GET', 'POST'])]
    public function new_hebergement(Request $request, EntityManagerInterface $entityManager): Response
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

        return $this->render('reservation_hebergement/new_hebergement.html.twig', [
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
        $form = $this->createForm(ReservationHebergementType::class, $reservationHebergement, [
            'hebergements' => $entityManager->getRepository(Hebergement::class)->findAll(),
        ]);
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
    
            // Make sure status is set
            if (!$reservationHebergement->getStatus()) {
                $reservationHebergement->setStatus('EnAttente');
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
   



    #[Route('/new-from-voyage/{idResV}', name: 'app_reservation_hebergement_new_from_voyage', methods: ['GET', 'POST'])]
    public function newFromVoyage(
        Request $request, 
        EntityManagerInterface $entityManager,
        Reservation_voyageRepository $reservationVoyageRepository,
        HebergementRepository $hebergementRepository,
        int $idResV
    ): Response {
        $reservationVoyage = $reservationVoyageRepository->find($idResV);
        
        if (!$reservationVoyage) {
            throw $this->createNotFoundException('No travel reservation found for id '.$idResV);
        }
        
        $reservationHebergement = new ReservationHebergement();
        $reservationHebergement->setReservationVoyage($reservationVoyage);
        
        // Set default dates based on flight dates
        $reservationHebergement->setDate($reservationVoyage->getId_vol()->getDateDepart());
        $reservationHebergement->setDateFin($reservationVoyage->getId_vol()->getDateArrival());
        
        // Set default status
        $reservationHebergement->setStatus('EnAttente');
        
        $form = $this->createForm(ReservationHebergementType::class, $reservationHebergement, [
            'hebergements' => $hebergementRepository->findAll()
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Calculate price based on duration and accommodation price
            $start = $reservationHebergement->getDate();
            $end = $reservationHebergement->getDateFin();
            $diff = $start->diff($end);
            $days = max(1, $diff->days);
            
            $reservationHebergement->setPrix(
                $reservationHebergement->getHebergement()->getPrix() * $days
            );
            
            $entityManager->persist($reservationHebergement);
            $entityManager->flush();
            
            $this->addFlash('success', 'Accommodation reservation created successfully!');
            
            if ($request->headers->get('Turbo-Frame')) {
                return $this->redirectToRoute('app_reservation_hebergement_show', [
                    'idResH' => $reservationHebergement->getIdResH()
                ], Response::HTTP_SEE_OTHER);
            }
            
            return $this->redirectToRoute('flight_history');
        }
        
        // Handle form errors
        $response = $this->render('Interface User/ReservationHebergement.twig', [
            'form' => $form->createView(),
            'reservation_voyage' => $reservationVoyage,
        ]);
        
        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        return $response;
    }


    #[Route('ListeReservationUser/{idResV}', name: 'DetailHebergementUser', methods: ['GET'])]
    public function Liste(
        int $idResV,
        EntityManagerInterface $entityManager
    ): Response {
        // Requête optimisée qui joint les deux entités
        $reservation_voyage = $entityManager->getRepository(Reservation_voyage::class)
            ->find($idResV);
        
        if (!$reservation_voyage) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }
    
        $reservation_hebergements = $entityManager->getRepository(ReservationHebergement::class)
            ->createQueryBuilder('rh')
            ->leftJoin('rh.hebergement', 'h')
            ->addSelect('h')
            ->where('rh.reservationVoyage = :voyage')
            ->setParameter('voyage', $reservation_voyage)
            ->orderBy('rh.date', 'ASC')
            ->getQuery()
            ->getResult();
    
        return $this->render('Interface User/ViewAccomondation.twig', [
            'reservation_voyage' => $reservation_voyage,
            'reservation_hebergements' => $reservation_hebergements,
        ]);
    }

    #[Route('/api/new', name: 'api_reservation_hebergement_new', methods: ['POST'])]
    public function apiNew(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Recherche ou création de l'hébergement
        $hebergement = $em->getRepository(Hebergement::class)
            ->findOneBy(['nom' => $data['hebergement'], 'adresse' => $data['adresse']]);
        if (!$hebergement) {
            $hebergement = new Hebergement();
            $hebergement->setNom($data['hebergement']);
            $hebergement->setAdresse($data['adresse']);
            $hebergement->setPrix($data['prix']);
            $hebergement->setType('Hotel');
            $hebergement->setDescription('Réservation via site');
            $em->persist($hebergement);
        }

        $reservation = new ReservationHebergement();
        $reservation->setHebergement($hebergement);
        $reservation->setDate(new \DateTime($data['dateDebut']));
        $reservation->setDateFin(new \DateTime($data['dateFin']));
        $reservation->setPrix($data['prix']);
        $reservation->setStatus('EnAttente');
        $em->persist($reservation);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }
}
