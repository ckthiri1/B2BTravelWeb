<?php

namespace App\Controller;

use App\Entity\Fidelite;
use App\Entity\Reservation_voyage;
use App\Entity\User;
use App\Entity\Vol;
use App\Repository\Reservation_voyageRepository;
use App\Repository\VolRepository;
use App\Service\MailApiReservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/Reservation')]
final class ReservationVolController extends AbstractController
{
    #[Route('/Page', name: 'reservation_page')]
    public function index(
        EntityManagerInterface $entityManager,
        VolRepository $volRepository,
        Security $security
    ): Response {
        return $this->handleReservationRequest($entityManager, $volRepository, $security, 'Interface User/ReservationVol.twig');
    }

    #[Route('/user/flights', name: 'user_flights')]
    public function userFlights(
        EntityManagerInterface $entityManager,
        VolRepository $volRepository,
        Security $security
    ): Response {
        return $this->handleReservationRequest($entityManager, $volRepository, $security, 'ReservationVol.twig');
    }

    private function handleReservationRequest(
        EntityManagerInterface $entityManager,
        VolRepository $volRepository,
        Security $security,
        string $template
    ): Response {
        // Get the currently logged-in user
        $user = $security->getUser();

        if (!$user instanceof User) {
            if ($template === 'ReservationVol.twig') {
                throw $this->createNotFoundException('User not found. Please log in.');
            }
            return $this->redirectToRoute('app_login');
        }

        // Get user's loyalty program data
        $fidelite = $entityManager->getRepository(Fidelite::class)->findOneBy(['idUser' => $user]);
        $remise = $fidelite ? $fidelite->getRemise() : 0;

        // Get non-reserved flights
        $flights = $volRepository->findNonReservedByUser($user);

        return $this->render($template, [
            'flights' => $flights,
            'current_date' => new \DateTime(),
            'user_remise' => $remise,
            'user' => $user // Pass user object to template
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_vol', methods: ['POST'])]
    public function deleteVol(
        int $id,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        // Get the currently logged-in user
        $user = $security->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'You must be logged in to delete flights'
            ], 401);
        }

        // Find the flight by ID
        $vol = $entityManager->getRepository(Vol::class)->find($id);

        if (!$vol) {
            return $this->json([
                'success' => false,
                'message' => 'Flight not found'
            ], 404);
        }

        // Check if the flight belongs to the current user
        if ($vol->getUser()->getUserId() !== $user->getUserId()) {
            return $this->json([
                'success' => false,
                'message' => 'You can only delete your own flights'
            ], 403);
        }

        // Check flight status
        if ($vol->getStatus() !== 'NON_RESERVER') {
            return $this->json([
                'success' => false,
                'message' => 'You can only delete non-reserved flights'
            ], 403);
        }

        try {
            // Perform the deletion
            $entityManager->remove($vol);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Flight successfully deleted'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error deleting flight: ' . $e->getMessage()
            ], 500);
        }
    }

    //get les voyage with status reserver

    #[Route('/historique', name: 'flight_history')]
    public function flightHistory(
        EntityManagerInterface $entityManager,
        Reservation_voyageRepository $reservationVoyageRepository,
        Security $security
    ): Response {
        // Get the currently authenticated user
        $user = $security->getUser();

        if (!$user instanceof User) {
            $this->addFlash('error', 'You must be logged in to view your flight history.');
            return $this->redirectToRoute('app_login');
        }

        // Get reservation_voyage records for this user with their associated flights
        $reservations = $reservationVoyageRepository->createQueryBuilder('rv')
            ->join('rv.id_vol', 'v')
            ->where('rv.id_user = :user')
            ->andWhere('v.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', Vol::STATUS_RESERVER)
            ->orderBy('rv.id_resV', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('Interface User/HistoriquePage.twig', [
            'reservations' => $reservations,
            'current_date' => new \DateTime(),
            'user' => $user
        ]);
    }

    // confirmer la réservation 

    #[Route("/Reservation/confirm-reservation", name: "confirm_reservation")]
    public function confirmReservation(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        MailApiReservation $mailService  // Add this parameter to inject the mail service
    ): JsonResponse {
        try {
            // Get authenticated user
            $user = $security->getUser();
    
            if (!$user instanceof User) {
                return $this->json([
                    'success' => false,
                    'error' => 'Authentication required'
                ], Response::HTTP_UNAUTHORIZED);
            }
    
            // Get and validate JSON data
            $content = $request->getContent();
            if (empty($content)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Empty request body'
                ], Response::HTTP_BAD_REQUEST);
            }
    
            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'success' => false,
                    'error' => 'Invalid JSON format: ' . json_last_error_msg()
                ], Response::HTTP_BAD_REQUEST);
            }
    
            $seatData = $data['seatData'] ?? [];
            if (empty($seatData)) {
                return $this->json([
                    'success' => false,
                    'error' => 'No flights selected'
                ], Response::HTTP_BAD_REQUEST);
            }
    
            $reservationIds = [];
            $reservationCodes = [];
            $sentEmails = []; // Track email sending status
    
            // Get user's discount from Fidelite
            $discount = 0;
            if ($data['applyDiscount'] ?? false) {
                $fidelite = $user->getFidelites()->first();
                if ($fidelite instanceof Fidelite) {
                    $discount = $fidelite->getRemise();
                }
            }
    
            // Begin transaction
            $entityManager->beginTransaction();
    
            try {
                foreach ($seatData as $reservationData) {
                    $volId = $reservationData['volId'] ?? null;
                    $seats = $reservationData['seats'] ?? 1;
    
                    if (!$volId) {
                        continue;
                    }
    
                    // Find flight
                    $vol = $entityManager->getRepository(Vol::class)->find($volId);
                    if (!$vol) {
                        throw new \Exception("Flight ID $volId not found");
                    }
    
                    // Create new reservation
                    $reservation = new Reservation_voyage();
                    $reservation->setId_user($user);
                    $reservation->setId_vol($vol);
                    $reservation->setPlace($seats);
                    
                    // Calculate total price with discount if applied
                    $totalPrice = $vol->getPrixVol() * $seats * (1 - $discount/100);
                    $reservation->setPrixTotal($totalPrice);
    
                    // Set payment details
                    $reservation->setPaymentMethod($data['paymentMethod'] ?? null);
                    $reservation->setPaymentStatus($data['paymentStatus'] ?? 'completed');
                    $reservation->setTransactionId($data['transactionId'] ?? null);
                    $reservation->setPaymentDate(new \DateTime());
    
                    // Generate unique reservation code
                    $reservationCode = strtoupper(bin2hex(random_bytes(4)));
                    $reservation->setReservationCode($reservationCode);
    
                    // Update flight status to RESERVER
                    $vol->setStatus(Vol::STATUS_RESERVER);
    
                    $entityManager->persist($reservation);
                    $entityManager->persist($vol);
    
                    // Update user's travel count
                    $user->setNbrVoyage(($user->getNbrVoyage() ?? 0) + 1);
    
                    // Get the ID and code
                    $entityManager->flush();
                    $reservationIds[] = $reservation->getId_resV();
                    $reservationCodes[] = $reservationCode;
                    
                    // Generate QR code paths for email
                    $qrCodePaths = [];
                    $qrCodeUrl = $this->generateUrl('reservation_qrcode', ['id' => $reservation->getId_resV()], UrlGeneratorInterface::ABSOLUTE_URL);
                    
                    // Since we're using URL-based QR codes, we'll just pass the URL for now
                    // You may need to actually generate and save QR code files if your MailApiReservation expects file paths
                    
                    // Send confirmation email for this reservation
                    $emailSent = $mailService->sendReservationConfirmation(
                        $user->getEmail(),
                        $user->getNom() ?? $user->getPrenom(),
                        $reservation,
                        $vol,
                        [$qrCodeUrl] // You might need to adjust this based on how your QR code system works
                    );
                    
                    $sentEmails[] = $emailSent;
                }
    
                // Commit transaction
                $entityManager->commit();
    
                return $this->json([
                    'success' => true,
                    'message' => 'Reservation confirmed successfully!',
                    'redirect' => $this->generateUrl('flight_history'),
                    'reservationIds' => $reservationIds,
                    'reservationCode' => $reservationCodes[0] ?? null,
                    'emailsSent' => !in_array(false, $sentEmails), // Check if all emails were sent successfully
                    'qrCodes' => array_map(function ($id) {
                        return $this->generateUrl('reservation_qrcode', ['id' => $id]);
                    }, $reservationIds)
                ], Response::HTTP_OK, ['Content-Type' => 'application/json']);
            } catch (\Exception $e) {
                // Rollback transaction on error
                $entityManager->rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            // Log the error
            error_log('Reservation error: ' . $e->getMessage());
    
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while processing your reservation',
                'debug' => $e->getMessage() // Remove in production
            ], Response::HTTP_INTERNAL_SERVER_ERROR, ['Content-Type' => 'application/json']);
        }
    }

    #[Route("/create-payment-intent", name: "create_payment_intent", methods: ["POST"])]
public function createPaymentIntent(Request $request): JsonResponse
{
    \Stripe\Stripe::setApiKey('sk_test_51RH6oTR8oYx3CziCmozzyu9FlWY9hW8kPAKHbLdhqvS39TIzp7ActyqoAG64FqeFClxubiz1mkYykqgP324Hs6A500ivg3bDHl'); // Replace with your actual test secret key
    
    try {
        $data = json_decode($request->getContent(), true);
        
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'description' => $data['description'],
            // 'customer' => 'cus_...', // You can associate with a Stripe customer
            'metadata' => [
                'user_id' => $this->getUser()->getUserIdentifier(),
                'integration_check' => 'accept_a_payment'
            ]
        ]);
        
        return $this->json(['clientSecret' => $paymentIntent->client_secret]);
    } catch (\Exception $e) {
        return $this->json(['error' => $e->getMessage()], 400);
    }
}

    #[Route('/reservation/qrcode/{id}', name: 'reservation_qrcode')]
    public function showQrCode(Reservation_voyage $reservation, Security $security): Response
    {
        // Security check - ensure user owns the reservation or is admin
        $user = $security->getUser();
        if ($user !== $reservation->getId_user() && !$security->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have access to this reservation');
        }
    
        // Generate QR code content with all necessary information
        $qrContent = json_encode([
            'reservationId' => $reservation->getId_resV(),
            'reservationCode' => $reservation->getReservationCode(),
            'flightNumber' => $reservation->getId_vol()->getFlightNumber(),
            'airline' => $reservation->getId_vol()->getAirLine(),
            'passenger' => $reservation->getId_user()->getNom() . ' ' . $reservation->getId_user()->getPrenom(),
            'departureCity' => $reservation->getId_vol()->getIdVoyage()->getDepart(),
            'destinationCity' => $reservation->getId_vol()->getIdVoyage()->getDestination(),
            'departureDate' => $reservation->getId_vol()->getDateDepart()->format('Y-m-d H:i'),
            'arrivalDate' => $reservation->getId_vol()->getDateArrival()->format('Y-m-d H:i'),
            'flightDuration' => $reservation->getId_vol()->getDureeVol(),
            'seats' => $reservation->getPlace(),
            'totalPrice' => $reservation->getPrixTotal(),
            'flightType' => $reservation->getId_vol()->getTypeVol()
        ]);
    
        return $this->render('Interface User/qr_code.html.twig', [
            'reservation' => $reservation,
            'qrContent' => $qrContent
        ]);
    }

    #[Route('/scan-reservation', name: 'scan_reservation')]
    public function scanReservation(): Response
    {
        return $this->render('reservation/scan.html.twig');
    }

    #[Route('/api/verify-reservation/{code}', name: 'verify_reservation', methods: ['GET'])]
    public function verifyReservation(string $code, EntityManagerInterface $em): JsonResponse
    {
        $reservation = $em->getRepository(Reservation_voyage::class)->findOneBy(['reservationCode' => $code]);

        if (!$reservation) {
            return $this->json(['error' => 'Reservation not found'], 404);
        }

        return $this->json([
            'reservationId' => $reservation->getId_resV(),
            'flightNumber' => $reservation->getId_vol()->getFlightNumber(),
            'passenger' => $reservation->getId_user()->getFullName(),
            'date' => $reservation->getId_vol()->getDateDepart()->format('Y-m-d'),
            'seats' => $reservation->getPlace(),
            'valid' => true
        ]);
    }


    //Admin Reservation Voyage

    #[Route('/reservationAdmin/qrcode/{id}', name: 'reservation_qrcode_admin')]
    public function showQrCodeAdmin(Reservation_voyage $reservation, Security $security): Response
    {
        // Security check - ensure user owns the reservation or is admin
        $user = $security->getUser();
        if ($user !== $reservation->getId_user() && !$security->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have access to this reservation');
        }
    
        // Generate QR code content with all necessary information
        $qrContent = json_encode([
            'reservationId' => $reservation->getId_resV(),
            'reservationCode' => $reservation->getReservationCode(),
            'flightNumber' => $reservation->getId_vol()->getFlightNumber(),
            'airline' => $reservation->getId_vol()->getAirLine(),
            'passenger' => $reservation->getId_user()->getNom() . ' ' . $reservation->getId_user()->getPrenom(),
            'departureCity' => $reservation->getId_vol()->getIdVoyage()->getDepart(),
            'destinationCity' => $reservation->getId_vol()->getIdVoyage()->getDestination(),
            'departureDate' => $reservation->getId_vol()->getDateDepart()->format('Y-m-d H:i'),
            'arrivalDate' => $reservation->getId_vol()->getDateArrival()->format('Y-m-d H:i'),
            'flightDuration' => $reservation->getId_vol()->getDureeVol(),
            'seats' => $reservation->getPlace(),
            'totalPrice' => $reservation->getPrixTotal(),
            'flightType' => $reservation->getId_vol()->getTypeVol()
        ]);
    
        return $this->render('Interface User/qr_code_admin.html.twig', [
            'reservation' => $reservation,
            'qrContent' => $qrContent
        ]);
    }

    #[Route('/ListeReservation', name: 'Liste_reservations')]
    public function reserv(Reservation_voyageRepository $reservationRepo): Response
    {
        // Get all reservations with user and flight details
        $reservations = $reservationRepo->findAllWithDetails();

        return $this->render('ReservationVoyage/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/reservation/details/{id}', name: 'reservation_details')]
    public function showDetails(Reservation_voyage $reservation): Response
    {
        return $this->render('ReservationVoyage/details.html.twig', [
            'reservation' => $reservation,
        ]);
    }

#[Route('/payment', name: 'payment_page')]
public function paymentPage(): Response
{
    return $this->render('Interface User/payement_page.twig');
}
}
