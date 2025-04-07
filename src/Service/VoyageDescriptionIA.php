<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class VoyageDescriptionIA
{
    private const API_URL = "https://api-inference.huggingface.co/models/tiiuae/falcon-7b-instruct";
    private const API_TOKEN = 'hello';//"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"; // Directly declare the token here

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function generateDescription(string $departure, string $arrival): string
    {
        try {
            $response = $this->client->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::API_TOKEN,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'inputs' => "Écris une description de voyage $departure à $arrival. ",
                    'max_new_tokens' => 150,
                ],
            ]);
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                return "Erreur: API a retourné un code $statusCode";
            }
            $content = $response->toArray();
            $generatedText = $content[0]['generated_text'] ?? "Aucune description générée.";
            $description = trim(preg_replace('/^.*?inputs:/', '', $generatedText));
            return $description ?: "Impossible de générer une description pour ce voyage.";
        } catch (\Exception $e) {
            return "Erreur lors de la génération de la description: " . $e->getMessage();
        }
    }
}