<?php
namespace App\Controller;

use App\Service\VolApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageUserController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $cities = [
            'Tunis', 'Dubai', 'Paris', 'Londres', 'Munich', 
            'Tokyo', 'Casablanca', 'Le Caire'
        ];

        $tripTypes = ['ALLER SIMPLE', 'ALLER-RETOUR'];

        return $this->render('Interface User/HomePage.twig', [
            'cities' => $cities,
            'tripTypes' => $tripTypes,
        ]);
    }

    #[Route('/search/flights', name: 'app_search_flights', methods: ['POST'])]
    public function searchFlights(Request $request, VolApi $flightService): Response
    {
        try {
            $departureCity = $request->request->get('departure_city');
            $arrivalCity = $request->request->get('arrival_city');
            $tripType = $request->request->get('trip_type') ?? 'ALLER SIMPLE';
            $departureDate = $request->request->get('departure_date');
            $returnDate = $request->request->get('return_date');
    
            if (!$departureCity || !$arrivalCity || !$departureDate) {
                throw new \InvalidArgumentException("Veuillez remplir tous les champs obligatoires.");
            }
    
            $departureIata = $flightService->getIataCode($departureCity);
            $arrivalIata = $flightService->getIataCode($arrivalCity);
    
            if (empty($departureIata) || empty($arrivalIata)) {
                throw new \RuntimeException("Les codes IATA sont invalides.");
            }
    
            $outboundFlights = $flightService->getFlights($departureIata, $arrivalIata, $departureDate);
            $processedOutboundFlights = $this->processFlights($outboundFlights, 'Aller');
    
            if (empty($processedOutboundFlights)) {
                $processedOutboundFlights = $this->getSampleFlights();
            }
    
            $processedReturnFlights = [];
            if ($tripType === 'ALLER-RETOUR' && !empty($returnDate)) {
                $returnFlights = $flightService->getFlights($arrivalIata, $departureIata, $returnDate);
                $processedReturnFlights = $this->processFlights($returnFlights, 'Retour');
    
                if (empty($processedReturnFlights)) {
                    $processedReturnFlights = $this->getSampleReturnFlights();
                }
            }
    
            // Check if request is AJAX
            if ($request->isXmlHttpRequest()) {
                return $this->render('Interface User/results.html.twig', [
                    'departureCity' => $departureCity,
                    'arrivalCity' => $arrivalCity,
                    'departureDate' => new \DateTime($departureDate),
                    'returnDate' => $returnDate ? new \DateTime($returnDate) : null,
                    'outboundFlights' => $processedOutboundFlights,
                    'returnFlights' => $processedReturnFlights,
                    'tripType' => $tripType
                ]);
            }
    
            return $this->redirectToRoute('app_home');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la recherche: ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }
    

    private function processFlights(array $flights, string $type): array
    {
        return array_map(function ($flight) use ($type) {
            return [
                'airline' => $flight['airline'] ?? 'Inconnu',
                'flightNumber' => $flight['flightNumber'] ?? 'N/A',
                'departureTime' => new \DateTime($flight['departureTime'] ?? 'now'),
                'arrivalTime' => new \DateTime($flight['arrivalTime'] ?? 'now'),
                'duration' => $flight['duration'] ?? 'Inconnu',
                'price' => $flight['price'] ?? rand(500, 1500),
                'type' => $type
            ];
        }, $flights);
    }

    private function getSampleFlights(): array
    {
        return [
            [
                'airline' => 'Tunisair',
                'flightNumber' => 'TU501',
                'departureTime' => new \DateTime('+1 day'),
                'arrivalTime' => (new \DateTime('+1 day'))->modify('+3 hours'),
                'duration' => '3h 15m',
                'price' => 650,
                'type' => 'Aller'
            ],
            [
                'airline' => 'Emirates',
                'flightNumber' => 'EK142',
                'departureTime' => new \DateTime('+2 days'),
                'arrivalTime' => (new \DateTime('+2 days'))->modify('+4 hours'),
                'duration' => '4h 30m',
                'price' => 850,
                'type' => 'Aller'
            ]
        ];
    }

    private function getSampleReturnFlights(): array
    {
        return [
            [
                'airline' => 'Air France',
                'flightNumber' => 'AF123',
                'departureTime' => new \DateTime('+5 days'),
                'arrivalTime' => (new \DateTime('+5 days'))->modify('+3 hours'),
                'duration' => '3h 10m',
                'price' => 700,
                'type' => 'Retour'
            ],
            [
                'airline' => 'Qatar Airways',
                'flightNumber' => 'QR456',
                'departureTime' => new \DateTime('+6 days'),
                'arrivalTime' => (new \DateTime('+6 days'))->modify('+4 hours'),
                'duration' => '4h 20m',
                'price' => 900,
                'type' => 'Retour'
            ]
        ];
    }
}
