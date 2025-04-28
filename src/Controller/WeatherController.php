<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class WeatherController extends AbstractController
{
    private $weatherService;
    private $logger;

    public function __construct(WeatherService $weatherService, LoggerInterface $logger)
    {
        $this->weatherService = $weatherService;
        $this->logger = $logger;
    }

    #[Route('/api/weather/{city}', name: 'app_weather', methods: ['GET'])]
    public function getWeather(string $city): JsonResponse
    {
        $this->logger->info("Weather API request for city: {$city}");
        
        if (empty($city)) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Le nom de la ville ne peut pas être vide'
            ], 400);
        }
        
        $weather = $this->weatherService->getWeatherForCity($city);
        
        if ($weather === null) {
            return new JsonResponse([
                'error' => true,
                'message' => 'Impossible de récupérer les données météo'
            ], 500);
        }

        // If there's an error in the weather data, return it with a 400 status
        if (isset($weather['error']) && $weather['error']) {
            return new JsonResponse($weather, 400);
        }

        return new JsonResponse($weather);
    }
} 