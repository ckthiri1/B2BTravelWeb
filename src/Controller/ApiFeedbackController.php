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

#[Route('/api/feedback')]
class ApiFeedbackController extends AbstractController
{
    #[Route('/{id}', name: 'api_feedback_create', methods: ['POST'])]
    public function create(
        Request $request,
        Reponse $reponse,
        SessionInterface $session,
        LoggerInterface $logger
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['error' => 'JSON invalide'], 400);
        }

        if (!isset($data['rating']) || !is_numeric($data['rating']) || 
            $data['rating'] < 1 || $data['rating'] > 5) {
            return $this->json(['error' => 'La note doit être entre 1 et 5'], 400);
        }

        $feedbacks = $session->get('feedbacks', []);
        $feedbackKey = 'feedback_'.$reponse->getId();
        
        $feedbacks[$feedbackKey] = [
            'reponse_id' => $reponse->getId(),
            'rating' => (int)$data['rating'],
            'comment' => $data['comment'] ?? null,
            'date' => date('Y-m-d H:i:s')
        ];
        
        $session->set('feedbacks', $feedbacks);

        $logger->info('Feedback reçu', [
            'reponse' => $reponse->getId(),
            'rating' => $data['rating']
        ]);

        return $this->json([
            'status' => 'success',
            'message' => 'Feedback enregistré',
            'feedback' => $feedbacks[$feedbackKey]
        ]);
    }

    #[Route('/check/{id}', name: 'api_feedback_check', methods: ['GET'])]
    public function checkFeedback(
        Reponse $reponse,
        SessionInterface $session
    ): JsonResponse {
        $feedbacks = $session->get('feedbacks', []);
        $feedbackKey = 'feedback_'.$reponse->getId();
        
        return $this->json([
            'hasFeedback' => isset($feedbacks[$feedbackKey]),
            'feedback' => $feedbacks[$feedbackKey] ?? null
        ]);
    }
}
