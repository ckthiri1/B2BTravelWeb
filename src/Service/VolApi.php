<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

class VolApi
{
    private HttpClientInterface $httpClient;
    private string $baseUrl = 'http://api.aviationstack.com/v1/flights';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getFlights(string $departureIata, string $arrivalIata): array
    {
        $apiKey = '2ef89ba496f5406e913fdbc9a12a6c2c'; 
        
        try {
            $url = sprintf(
                '%s?access_key=%s&dep_iata=%s&arr_iata=%s', 
                $this->baseUrl, 
                $apiKey, 
                $departureIata, 
                $arrivalIata
            );

            $response = $this->httpClient->request('GET', $url);
            $data = $response->toArray();

            return $this->parseFlightData($data);
        } catch (\Exception $e) {
            // Log or handle the error appropriately
            return [];
        }
    }

    private function parseFlightData(array $apiData): array
    {
        $flights = [];

        if (!isset($apiData['data'])) {
            return $flights;
        }

        foreach ($apiData['data'] as $flight) {
            $flights[] = [
                'airline' => $flight['airline']['name'] ?? 'Unknown',
                'flightNumber' => $flight['flight']['number'] ?? '',
                'departureTime' => $flight['departure']['scheduled'] ?? '',
                'arrivalTime' => $flight['arrival']['scheduled'] ?? '',
                'duration' => $flight['flight_time'] ?? '',
                'price' => rand(500, 1500) // Simulated price
            ];
        }

        return $flights;
    }

    public function getIataCode(string $city): string
    {
        $cityToIata = [
            'Tunis' => 'TUN',
            'Dubai' => 'DXB',
            'Paris' => 'CDG',
            'London' => 'LHR', 
            'Munich' => 'MUC',
            'Tokyo' => 'HND',
            'Casablanca' => 'CMN',
            'Cairo' => 'CAI'  
        ];
        
        // Add case-insensitive matching
        $city = ucfirst(strtolower(trim($city)));
        
        return $cityToIata[$city] ?? '';
    }
}