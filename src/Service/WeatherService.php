<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class WeatherService
{
    private $httpClient;
    private $apiKey;
    private $apiUrl = 'https://api.openweathermap.org/data/2.5/weather';
    private $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $_ENV['OPENWEATHER_API_KEY'] ?? '';
        
        if (empty($this->apiKey)) {
            $this->logger->error('OpenWeather API key is not set in .env file');
        }
    }

    public function getWeatherForCity(string $city): ?array
    {
        if (empty($this->apiKey)) {
            $this->logger->error('Cannot fetch weather: OpenWeather API key is not set');
            return null;
        }

        try {
            $this->logger->info("Attempting to fetch weather for city: {$city}");
            
            $response = $this->httpClient->request('GET', $this->apiUrl, [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr'
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent();
            $data = json_decode($content, true);
            
            $this->logger->info("Weather API response status: {$statusCode}");
            
            if ($statusCode === 401) {
                $this->logger->error("API Key unauthorized. Please check if the API key is valid and activated.");
                return [
                    'error' => true,
                    'message' => 'Clé API non valide ou non activée. Veuillez patienter 2-4 heures après la création de la clé.'
                ];
            }

            if ($statusCode === 200 && isset($data['main']) && isset($data['weather'][0])) {
                $this->logger->info("Successfully retrieved weather data for {$city}");
                return [
                    'error' => false,
                    'temperature' => round($data['main']['temp']),
                    'description' => $data['weather'][0]['description'],
                    'icon' => $data['weather'][0]['icon'],
                    'humidity' => $data['main']['humidity'],
                    'wind_speed' => $data['wind']['speed'] ?? 0
                ];
            } else {
                $this->logger->error("Invalid weather data structure for {$city}: " . json_encode($data));
                return [
                    'error' => true,
                    'message' => 'Données météo non disponibles pour cette ville.'
                ];
            }
        } catch (HttpExceptionInterface $e) {
            $this->logger->error("HTTP error fetching weather for {$city}: " . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Erreur de connexion à l\'API météo.'
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error fetching weather for {$city}: " . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Une erreur s\'est produite lors de la récupération des données météo.'
            ];
        }
    }
} 