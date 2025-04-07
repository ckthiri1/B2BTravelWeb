<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\FideliteRepository;
use App\Service\FideliteService;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FideliteController extends AbstractController {

    private HttpClientInterface $httpClient;
    private const API_TOKEN = "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";
    private const MODEL_NAME = "google/flan-t5-large";
    private $fideliteService;
    private $userService;
    private LoggerInterface $logger;
    private $fideliteRepository;

    public function __construct(HttpClientInterface $httpClient, FideliteService $fideliteService, UserService $userService, LoggerInterface $logger , FideliteRepository $fideliteRepository)
    {
        $this->httpClient = $httpClient;
        $this->fideliteService = $fideliteService;
        $this->userService = $userService;
        $this->logger = $logger;
        $this->fideliteRepository = $fideliteRepository;
    }  

    #[Route('/chatbot', name: 'chatbot_query', methods: ['POST'])]
    public function chatbotQuery(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? '';

        if (empty($question)) {
            return new JsonResponse(['response' => 'Veuillez poser une question.'], 400);
        }

        if (!$this->isRankRelatedQuestion($question)) {
            return new JsonResponse(['response' => "Je ne peux répondre qu'aux questions sur le programme de fidélité."], 200);
        }

        $response = $this->getChatbotResponseFromAI($question);
        return new JsonResponse(['response' => $response]);
    }

    private function isRankRelatedQuestion(string $question): bool
    {
        $keywords = ['rank', 'rang', 'fidelite', 'fidélité', 'point', 'discount', 'remise', 'voyage', 'silver', 'gold', 'platinuim', 'radiant'];
        return (bool) array_filter($keywords, fn($kw) => stripos($question, $kw) !== false);
    }

    private function getChatbotResponseFromAI(string $question): string
    {
        // Enhanced prompt with clearer instructions and examples
        $prompt = "Tu es un assistant intelligent du programme de fidélité des voyages. Réponds de façon précise et directe.

INFORMATIONS SUR LE SYSTÈME DE RANGS:
- (Silver) Rank 1 : 0-9 points, jusqu'à 0.9% de remise
- (Gold) Rank 2 : 10-14 points, au moins 1.0% de remise
-  (Platinuim) Rank 3: 15-19 points, au moins 1.5% de remise
-  (Radiant) Rank 4: 20+ points, au moins 2.0% de remise
- Formule pour calculer les points: Points = Nombre de voyages ÷ 2
- Formule pour calculer la remise: Remise = (Points ÷ 3) × 0.3%

INSTRUCTIONS:
- Quand on te demande quel est le meilleur rang, réponds 'Radiant' (Rank 4)
- Quand on demande combien de points pour un rang spécifique, donne le nombre exact
- Utilise toujours les noms des rangs (Silver, Gold, Platinuim, Radiant) dans tes réponses
- Donne des réponses concises et précises
- Si on te demande un calcul, montre la formule et le résultat

EXEMPLES:
- Question: 'Quel est le meilleur rank?'
  Réponse: 'Le meilleur rank est Radiant (Rank 4), qui offre au moins 2.0% de remise pour les clients ayant 20 points ou plus.'
  
- Question: 'Combien de voyages pour avoir le rang gold?'
  Réponse: 'Pour atteindre le rang Gold (Rank 2), vous avez besoin de 10-14 points. Comme Points = Voyages ÷ 2, il vous faut entre 20 et 28 voyages.'

Question : " . $question;

        try {
            $response = $this->httpClient->request('POST', "https://api-inference.huggingface.co/models/" . self::MODEL_NAME, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::API_TOKEN,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['inputs' => $prompt],
            ]);

            $data = $response->toArray();
            return $data[0]['generated_text'] ?? 'Je ne peux pas répondre à cette question.';
        } catch (\Exception $e) {
            return "Service indisponible pour le moment.";
        }
    }
    

    #[Route('/fidelite', name: 'app_fidelite')]
    public function index(): Response
    {
        $userId = 32;
    
        try {
            $user = $this->userService->getUserById($userId);
            if (!$user) {
                $this->addFlash('error', 'User not found.');
                return $this->redirectToRoute('app_home');
            }
    
            // Just ensure fidelite is calculated/updated based on current state
            $this->checkAndUpdateFidelite($user);
    
            $fidelite = $this->fideliteRepository->getFideliteWithRankByUser($userId);
    
            return $this->render('Interface User/FidelitePage.twig', [
                'user' => $user,
                'fidelite' => $fidelite,
            ]);
    
        } catch (\Exception $e) {
            error_log('❌ EXCEPTION in fidelite controller: ' . $e->getMessage());
            return new Response('Error: ' . $e->getMessage(), 500);
        }
    }
    
    private function checkAndUpdateFidelite(User $user): void
    {
        try {
            $fidelite = $this->fideliteService->getFideliteByUser($user->getUser_id());
    
            if ($fidelite === null) {
                $this->fideliteService->addFidelite($user);
            } else {
                $this->fideliteService->updateFidelite($user);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Error checking/updating fidelity: ' . $e->getMessage());
        }
    }
    

}