<?php

namespace App\Controller;

use App\Entity\Fidelite;
use App\Entity\User;
use App\Repository\FideliteRepository;
use App\Service\FideliteService;
use App\Service\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
    public function chatbotQuery(Request $request, FideliteRepository $fideliteRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = trim($data['question'] ?? '');

        if (empty($question)) {
            return new JsonResponse(['response' => 'Please ask a question about our loyalty program.'], 400);
        }

        $user = $this->getUser();
        $fidelite = $fideliteRepository->findOneBy(['idUser' => $user]);
        
        $response = $this->generateChatbotResponse($question, $fidelite);
        
        return new JsonResponse(['response' => $response]);
    }

    private function generateChatbotResponse(string $question, ?Fidelite $fidelite): string
    {
        $normalizedQuestion = mb_strtolower(trim($question));
        
        // Enhanced current rank response with more context
        if (preg_match('/.*(my|mon).*(rank|rang).*/i', $normalizedQuestion) && $fidelite) {
            $rank = $fidelite->getIdRank();
            $points = $fidelite->getPoints();
            $discount = $fidelite->getRemise();
            $trips = $points * 2; // Assuming points = trips / 2
            
            $response = "Your current rank is {$rank->getNomRank()} with {$points} points (giving you {$discount}% discount). ";
            $response .= "You've completed approximately {$trips} trips.\n\n";
            
            if ($rank->getNomRank() === 'SILVER') {
                $response .= "As a Silver member, you're at the beginning of your loyalty journey. ";
                $response .= "To reach Gold rank, you'll need ".(10 - $points)." more points (about ".( (10 - $points) * 2 )." trips).\n\n";
            } elseif ($rank->getNomRank() === 'GOLD') {
                $response .= "Great job reaching Gold status! You're halfway to Radiant. ";
                $response .= "Just ".(15 - $points)." more points (about ".( (15 - $points) * 2 )." trips) to reach Platinum rank!\n\n";
            } elseif ($rank->getNomRank() === 'PLATINUM') {
                $response .= "Impressive! You've achieved Platinum status. ";
                $response .= "Only ".(20 - $points)." more points (about ".( (20 - $points) * 2 )." trips) to reach the highest Radiant rank!\n\n";
            } elseif ($rank->getNomRank() === 'RADIANT') {
                $response .= "Congratulations! You've reached our highest Radiant status. ";
                $response .= "Keep traveling to maintain your exclusive benefits!\n\n";
            }
            
            $response .= "Our loyalty ranks:\n";
            $response .= "1. Silver (0-9 pts): Up to 0.9% discount\n";
            $response .= "2. Gold (10-14 pts): 1.0-1.4% discount\n";
            $response .= "3. Platinum (15-19 pts): 1.5-1.9% discount\n";
            $response .= "4. Radiant (20+ pts): 2.0%+ discount\n\n";
            $response .= "Points are calculated as: Number of Trips ÷ 2";
            
            return $response;
        }
        
        // Handle other specific question patterns...
        
        // Fallback to AI with enhanced prompt
        return $this->getChatbotResponseFromAI($question, $fidelite);
    }

    private function getChatbotResponseFromAI(string $question, ?Fidelite $fidelite): string
    {
        $userContext = "";
        $rankInfo = "";
        
        if ($fidelite) {
            $rank = $fidelite->getIdRank();
            $userContext = sprintf(
                "USER'S CURRENT STATUS: Points: %d, Discount: %.1f%%, Rank: %s\n",
                $fidelite->getPoints(),
                $fidelite->getRemise(),
                $rank ? $rank->getNomRank() : 'None'
            );
            
            $rankInfo = "Current Rank Explanation:\n";
            if ($rank) {
                switch ($rank->getNomRank()) {
                    case 'SILVER':
                        $rankInfo .= "- Silver: Entry level, 0-9 points, up to 0.9% discount\n";
                        $rankInfo .= "- Next rank: Gold at 10 points\n";
                        break;
                    case 'GOLD':
                        $rankInfo .= "- Gold: Mid tier, 10-14 points, 1.0-1.4% discount\n";
                        $rankInfo .= "- Next rank: Platinum at 15 points\n";
                        break;
                    case 'PLATINUM':
                        $rankInfo .= "- Platinum: Premium, 15-19 points, 1.5-1.9% discount\n";
                        $rankInfo .= "- Next rank: Radiant at 20 points\n";
                        break;
                    case 'RADIANT':
                        $rankInfo .= "- Radiant: Elite, 20+ points, 2.0%+ discount\n";
                        $rankInfo .= "- Highest rank achieved\n";
                        break;
                }
            }
        }

        $prompt = <<<PROMPT
You are an intelligent assistant for B2B Travel's loyalty program. Provide detailed but concise answers.

LOYALTY PROGRAM STRUCTURE:
1. Silver (Rank 1): 0-9 points, up to 0.9% discount
2. Gold (Rank 2): 10-14 points, 1.0-1.4% discount
3. Platinum (Rank 3): 15-19 points, 1.5-1.9% discount  
4. Radiant (Rank 4): 20+ points, 2.0%+ discount

POINTS SYSTEM:
- Earn 0.5 points per trip (Points = Trips ÷ 2)
- Discount formula: (Points ÷ 3) × 0.3%

{$userContext}
{$rankInfo}
INSTRUCTIONS:
1. Always mention the complete rank structure when explaining ranks
2. For rank questions, compare to next available rank
3. Include specific examples when explaining calculations
4. Keep responses friendly but professional
5. Highlight benefits of higher ranks

QUESTION: {$question}
PROMPT;

        try {
            $response = $this->httpClient->request('POST', "https://api-inference.huggingface.co/models/" . self::MODEL_NAME, [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::API_TOKEN,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['inputs' => $prompt],
            ]);

            $data = $response->toArray();
            return $data[0]['generated_text'] ?? "I can explain our loyalty program: We have 4 ranks (Silver, Gold, Platinum, Radiant) with increasing benefits. Your rank depends on points earned from trips.";
        } catch (\Exception $e) {
            return "Our loyalty program has 4 ranks:\n1. Silver (0-9 pts)\n2. Gold (10-14 pts)\n3. Platinum (15-19 pts)\n4. Radiant (20+ pts)\n\nYou earn 0.5 points per trip (Trips ÷ 2)";
        }
    }
  
    #[Route('/fidelite', name: 'app_fidelite')]
    public function index(Security $security): Response
    {
        // Get the currently authenticated user
        $user = $security->getUser();
        
        if (!$user instanceof User) {
            $this->addFlash('error', 'You must be logged in to view your fidelity program.');
            return $this->redirectToRoute('app_login');
        }
    
        try {
            // Ensure fidelite is calculated/updated based on current state
            $this->checkAndUpdateFidelite($user);
    
            $fidelite = $this->fideliteRepository->getFideliteWithRankByUser($user->getUserId());
    
            return $this->render('Interface User/FidelitePage.twig', [
                'user' => $user,
                'fidelite' => $fidelite,
            ]);
    
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while loading your fidelity information.');
            error_log('❌ EXCEPTION in fidelite controller: ' . $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }
    
    private function checkAndUpdateFidelite(User $user): void
    {
        try {
            $fidelite = $this->fideliteService->getFideliteByUser($user->getUserId());
    
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