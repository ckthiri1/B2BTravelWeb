<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class QuoteController extends AbstractController
{
    #[Route('/api/quote', name: 'api_quote')]
    public function quote(HttpClientInterface $client): JsonResponse
    {
        $response = $client->request('GET', 'https://zenquotes.io/api/random');
        $data = $response->toArray();
        return $this->json($data[0]);
    }
}