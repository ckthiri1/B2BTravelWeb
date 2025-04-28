<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Vol;
use App\Entity\Voyage;
use App\Repository\UserRepository;
use App\Repository\VolRepository;
use App\Repository\VoyageRepository;
use App\Service\VolApi;
use Doctrine\ORM\EntityManagerInterface;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class VolController extends AbstractController
{
    private $logger;
    private $entityManager;
    private $voyageRepository;
    private $volRepository;
    private $userRepository;
    

    public function __construct(LoggerInterface $logger,  EntityManagerInterface $entityManager,  VoyageRepository $voyageRepository,
    VolRepository $volRepository,
    UserRepository $userRepository)
    {
        $this->logger = $logger;

        $this->entityManager = $entityManager;
        $this->voyageRepository = $voyageRepository;
        $this->volRepository = $volRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('/voyage/{id}/flights', name: 'app_voyage_flights')]
    public function showFlights(Voyage $voyage, VolApi $flightService, Request $request, SessionInterface $session): Response
    {
    
        if (!$session->isStarted()) {
            $session->start();
        }

        $this->logger->info('Starting flight search for voyage', ['voyage_id' => $voyage->getVID()]);

        try {
            $departureCity = $voyage->getDepart();
            $destinationCity = $voyage->getDestination();

            $this->logger->debug('Getting IATA codes', [
                'departure_city' => $departureCity,
                'destination_city' => $destinationCity
            ]);

            $departureIata = $flightService->getIataCode($departureCity);
            $arrivalIata = $flightService->getIataCode($destinationCity);

            $this->logger->debug('Received IATA codes', [
                'departure_iata' => $departureIata,
                'arrival_iata' => $arrivalIata
            ]);

            if (empty($departureIata) || empty($arrivalIata)) {
                throw new \RuntimeException("Invalid IATA codes.");
            }

            $this->logger->info('Fetching flights', [
                'departure' => $departureIata,
                'destination' => $arrivalIata
            ]);

            $flights = $flightService->getFlights($departureIata, $arrivalIata);

            $processedFlights = array_map(function ($flight) {
                return [
                    'airline' => $flight['airline'] ?? 'Unknown',
                    'flightNumber' => $flight['flightNumber'] ?? 'N/A',
                    'departureTime' => $flight['departureTime'] ?? 'N/A',
                    'arrivalTime' => $flight['arrivalTime'] ?? 'N/A',
                    'duration' => $flight['duration'] ?? 'Unknown',
                    'price' => $flight['price'] ?? rand(500, 1500),
                    'type' => $flight['type'] ?? 'Aller'
                ];
            }, $flights);

            if (empty($processedFlights)) {
                $this->logger->warning('No flights found, showing sample data');
                $this->addFlash('warning', 'No flights found. Showing sample data.');
                $processedFlights = $this->getSampleFlights();
            }

            return $this->render('vol/index.html.twig', [
                'flights' => $processedFlights,
                'voyage' => $voyage
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    private function getSampleFlights(): array
    {
        return [
            [
                'airline' => 'Tunisair',
                'flightNumber' => 'TU501',
                'departureTime' => (new \DateTime('+1 day'))->format('Y-m-d\TH:i:s'),
                'arrivalTime' => (new \DateTime('+1 day +3 hours'))->format('Y-m-d\TH:i:s'),
                'duration' => '3h 15m',
                'price' => 650,
                'type' => 'Aller'
            ],
            [
                'airline' => 'Emirates',
                'flightNumber' => 'EK142',
                'departureTime' => (new \DateTime('+2 days'))->format('Y-m-d\TH:i:s'),
                'arrivalTime' => (new \DateTime('+2 days +4 hours'))->format('Y-m-d\TH:i:s'),
                'duration' => '4h 30m',
                'price' => 850,
                'type' => 'Aller'
            ]
        ];
    }


#[Route('/voyage/{id}/flights/pdf', name: 'app_vol_export_pdf')]
public function exportFlightsPdf(Voyage $voyage, VolApi $flightService): Response
{
    $departureIata = $flightService->getIataCode($voyage->getDepart());
    $arrivalIata = $flightService->getIataCode($voyage->getDestination());
    
    $flights = $flightService->getFlights($departureIata, $arrivalIata);
    
    // Get absolute file paths - ADD THIS CODE HERE
    $projectDir = $this->getParameter('kernel.project_dir');
    $logoPath = $projectDir . '/public/images/logo.png';
    $qrPath = $projectDir . '/public/images/qrcode.png';
    $signaturePath = $projectDir . '/public/images/signature.png';
    
    // Create new TCPDF document
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Travel Agency');
    $pdf->SetTitle('Flight List');
    $pdf->SetSubject('Flights from ' . $voyage->getDepart() . ' to ' . $voyage->getDestination());
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage('L', 'A4');
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Generate HTML content - UPDATE THIS LINE
    $html = $this->renderView('vol/pdf.html.twig', [
        'flights' => $flights,
        'voyage' => $voyage,
        'logo_path' => $logoPath,
        'qr_path' => $qrPath,
        'signature_path' => $signaturePath,
    ]);
    
    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $pdfContent = $pdf->Output('flights.pdf', 'S');
    
    return new Response(
        $pdfContent,
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="flights-' . $voyage->getDepart() . '-' . $voyage->getDestination() . '.pdf"',
        ]
    );
}

#[Route('/reservation/vol', name: 'reservation_vol', methods: ['POST'])]
public function reserverVol(
    Request $request, 
    EntityManagerInterface $entityManager,
    Security $security
): JsonResponse {
    try {
        // Verify Content-Type is application/json
        if ($request->getContentType() !== 'json' || !$request->getContent()) {
            return new JsonResponse(['error' => 'Invalid Content-Type or empty body'], 400);
        }

        // Get authenticated user
        $user = $security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentication required'], 401);
        }

        // Decode JSON data
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON format'], 400);
        }

        // Validate required fields
        $requiredFields = ['airline', 'flightNumber', 'price', 'departureTime', 'arrivalTime', 'depart', 'destination'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return new JsonResponse(['error' => "Missing required field: $field"], 400);
            }
        }

        // Parse dates
        $departureDatetime = new \DateTime($data['departureTime']);
        $arrivalDatetime = new \DateTime($data['arrivalTime']);

        // Find or create voyage
        $voyage = $entityManager->getRepository(Voyage::class)->findOneBy([
            'depart' => $data['depart'],
            'Destination' => $data['destination']
        ]);
        
        if (!$voyage) {
            $voyage = new Voyage();
            $voyage->setDepart($data['depart']);
            $voyage->setDestination($data['destination']);
            $voyage->setDescription("Vol de {$data['depart']} à {$data['destination']}");
            $entityManager->persist($voyage);
        }

        // Create new flight
        $vol = new Vol();
        
        // Generate new flight ID
        $lastVol = $entityManager->getRepository(Vol::class)
            ->findOneBy([], ['volID' => 'DESC']);
        $newVolId = $lastVol ? $lastVol->getVolID() + 1 : 1;
        
        // Set flight properties
        $vol->setVolID($newVolId);
        $vol->setDateDepart($departureDatetime);
        $vol->setDateArrival($arrivalDatetime);
        $vol->setAirLine($data['airline']);
        $vol->setFlightNumber((int)$data['flightNumber']);
        $vol->setDureeVol($departureDatetime->diff($arrivalDatetime)->format('%h hours %i minutes'));
        $vol->setPrixVol((int)preg_replace('/[^0-9]/', '', $data['price']));
        $vol->setTypeVol($data['typeVol'] ?? 'ALLER');
        $vol->setIdVoyage($voyage);
        $vol->setUser($user);
        $vol->setStatus('NON_RESERVER');
        
        
        $entityManager->persist($vol);
        $entityManager->persist($user);
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'message' => 'Flight reserved successfully',
            'volId' => $vol->getVolID(),
            'flightNumber' => $data['flightNumber']
        ]);
        
    } catch (\Exception $e) {
        return new JsonResponse([
            'error' => 'Reservation error',
            'message' => $e->getMessage()
        ], 500);
    }
}


}
