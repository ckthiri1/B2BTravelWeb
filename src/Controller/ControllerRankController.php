<?php

namespace App\Controller;

use App\Entity\Rank;
use App\Form\RankType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rank')]
final class ControllerRankController extends AbstractController
{



    #[Route('/count', name: 'app_rank_count', methods: ['GET'])]
public function countRanks(EntityManagerInterface $entityManager): JsonResponse
{
    $rankCount = $entityManager->getRepository(Rank::class)->count([]);
    
    return new JsonResponse(['rankCount' => $rankCount]);
}

    #[Route('/ListRank', name: 'app_controller_rank_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $ranks = $entityManager
            ->getRepository(Rank::class)
            ->findAll();

        return $this->render('rank/index.html.twig', [
            'ranks' => $ranks,
        ]);
    }

    #[Route('/newRank', name: 'app_controller_rank_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rank = new Rank();
        $form = $this->createForm(RankType::class, $rank);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Check if a rank with the same name already exists
            $existingRank = $entityManager->getRepository(Rank::class)->findOneBy([
                'NomRank' => $rank->getNomRank()
            ]);
    
            if ($existingRank) {
                $this->addFlash('error', 'This rank name already exists.');
            } else {
                $entityManager->persist($rank);
                $entityManager->flush();
    
                $this->addFlash('success', 'Rank created successfully.');
    
                return $this->redirectToRoute('app_controller_rank_index', [], Response::HTTP_SEE_OTHER);
            }
        }
    
        return $this->render('rank/new.html.twig', [
            'rank' => $rank,
            'form' => $form,
        ]);
    }
    

    #[Route('/{IDRang}', name: 'app_controller_rank_show', methods: ['GET'])]
    public function show(Rank $rank): Response
    {
        return $this->render('rank/show.html.twig', [
            'rank' => $rank,
        ]);
    }

    #[Route('/{IDRang}/edit', name: 'app_controller_rank_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Rank $rank, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RankType::class, $rank);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_controller_rank_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('rank/edit.html.twig', [
            'rank' => $rank,
            'form' => $form,
        ]);
    }

    #[Route('/{IDRang}', name: 'app_controller_rank_delete', methods: ['POST'])]
    public function delete(Request $request, Rank $rank, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rank->getIDRang(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rank);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_controller_rank_index', [], Response::HTTP_SEE_OTHER);
    }
}
