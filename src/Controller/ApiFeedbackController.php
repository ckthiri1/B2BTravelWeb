<?php

// src/Controller/ApiFeedbackController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use App\Entity\Reponse;

#[Route('/api/feedback')] // Chemin de base corrigé
class ApiFeedbackController extends AbstractController
{
    #[Route('/{id}', name: 'api_feedback_create', methods: ['POST'])] // Paramètre {id} ajouté
    public function create(
        Request $request,
        Reponse $reponse, // Injection de l'entité Reponse
        SessionInterface $session, // Injection du service Session
        LoggerInterface $logger
    ): JsonResponse {
        // Décodage JSON sécurisé
        $data = json_decode($request->getContent(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['error' => 'JSON invalide'], 400);
        }

        // Validation améliorée
        if (!isset($data['rating']) || !is_numeric($data['rating']) || 
            $data['rating'] < 1 || $data['rating'] > 5) {
            return $this->json(['error' => 'La note doit être entre 1 et 5'], 400);
        }

        // Stockage en session
        $feedbacks = $session->get('feedbacks', []);
        $feedbacks[] = [
            'reponse_id' => $reponse->getId(),
            'rating' => (int)$data['rating'],
            'comment' => $data['comment'] ?? null,
            'date' => date('Y-m-d H:i:s')
        ];
        $session->set('feedbacks', $feedbacks);

        // Logging
        $logger->info('Feedback reçu', [
            'reponse' => $reponse->getId(),
            'rating' => $data['rating']
        ]);

        return $this->json([
            'status' => 'success',
            'message' => 'Feedback enregistré'
        ]);
    }
}