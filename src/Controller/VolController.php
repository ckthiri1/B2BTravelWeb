<?php

namespace App\Controller;

use App\Entity\Voyage;
use App\Repository\VolRepository;
use App\Service\VolApi;
use Dompdf\Dompdf;
use Dompdf\Options;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class VolController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
    $flights = $flightService->getFlights(
        $flightService->getIataCode($voyage->getDepart()),
        $flightService->getIataCode($voyage->getDestination())
    );

    $html = $this->renderView('vol/pdf.html.twig', [
        'flights' => $flights,
        'voyage' => $voyage
    ]);

    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Arial');

    $dompdf = new Dompdf($pdfOptions);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $response = new Response($dompdf->output());
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', ResponseHeaderBag::DISPOSITION_ATTACHMENT . '; filename="flights.pdf"');

    return $response;
}

}
