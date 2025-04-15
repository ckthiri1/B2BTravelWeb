<?php

namespace App\Controller;

use App\Entity\Hebergement;
use App\Form\HebergementType;
use App\Repository\HebergementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/hebergement')]
final class HebergementController extends AbstractController
{
    #[Route('/export-pdf', name: 'app_hebergement_export_pdf', methods: ['GET'])]
    public function exportPdf(HebergementRepository $repo): Response
    {
        // Configuration de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // Récupération des données
        $hebergements = $repo->findAll();
        
        // Génération du HTML
        $html = $this->renderView('hebergement/pdf.html.twig', [
            'hebergements' => $hebergements,
            'date_export' => new \DateTime()
        ]);
        
        // Conversion en PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Envoi du PDF en réponse
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="hebergements-'.date('Y-m-d').'.pdf"'
            ]
        );
    }
    #[Route(name: 'app_hebergement_index', methods: ['GET'])]
public function index(Request $request, HebergementRepository $repo): Response
{
    $filters = [
        'type' => $request->query->get('type') ?? null,
        'sortPrice' => $request->query->get('sortPrice') ?? null,
        'address' => $request->query->get('address') ?? null,
        'searchTerm' => $request->query->get('searchTerm') ?? null
    ];

    return $this->render('hebergement/index.html.twig', [
        'hebergements' => $repo->search(
            $filters['type'],
            $filters['sortPrice'],
            $filters['address'],
            $filters['searchTerm']
        ),
        'stats' => [
            'count_by_type' => $repo->getCountByType(),
            'avg_price_by_type' => $repo->getAveragePriceByType(),
            'price_stats' => $repo->getPriceStatistics(),
        ],
        'currentFilters' => $filters
    ]);
}

    #[Route('/new', name: 'app_hebergement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hebergement = new Hebergement();
        $form = $this->createForm(HebergementType::class, $hebergement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hebergement);
            $entityManager->flush();

            return $this->redirectToRoute('app_hebergement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hebergement/new.html.twig', [
            'hebergement' => $hebergement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hebergement_show', methods: ['GET'])]
    public function show(Hebergement $hebergement): Response
    {
        return $this->render('hebergement/show.html.twig', [
            'hebergement' => $hebergement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hebergement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hebergement $hebergement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HebergementType::class, $hebergement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_hebergement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hebergement/edit.html.twig', [
            'hebergement' => $hebergement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_hebergement_delete', methods: ['POST'])]
    public function delete(Request $request, Hebergement $hebergement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hebergement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($hebergement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hebergement_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/prix', name: 'app_hebergement_prix', methods: ['GET'])]
    public function getPrix(Hebergement $hebergement): JsonResponse
    {
        $response = $this->json([
            'prix' => $hebergement->getPrix() ?? 0
        ]);
        
        // Autoriser les requêtes CORS
        $response->headers->set('Access-Control-Allow-Origin', '*');
        
        return $response;
    }
   
}
