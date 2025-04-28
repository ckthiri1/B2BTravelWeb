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
public function index(Request $request, EntityManagerInterface $entityManager): Response
{
    $sort = $request->query->get('sort');
    $searchTerm = $request->query->get('search');
    $rankRepository = $entityManager->getRepository(Rank::class);

    // Default sorting if no parameter is provided
    $orderBy = [];
    
    // Apply sorting based on the 'sort' parameter
    if ($sort === 'asc') {
        $orderBy = ['points' => 'ASC'];
    } elseif ($sort === 'desc') {
        $orderBy = ['points' => 'DESC'];
    }

    // Search functionality
    if ($searchTerm) {
        $ranks = $rankRepository->createQueryBuilder('r')
            ->where('r.NomRank LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%')
            ->orderBy('r.points', $orderBy['points'] ?? 'ASC')
            ->getQuery()
            ->getResult();
    } else {
        $ranks = $rankRepository->findBy([], $orderBy);
    }

    // Handle AJAX autocomplete requests
    if ($request->isXmlHttpRequest()) {
        $results = $rankRepository->createQueryBuilder('r')
            ->select('r.NomRank as text', 'r.IDRang as id')
            ->where('r.NomRank LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
        
        return new JsonResponse($results);
    }

    return $this->render('rank/index.html.twig', [
        'ranks' => $ranks,
        'searchTerm' => $searchTerm
    ]);
}

    #[Route('/newRank', name: 'app_controller_rank_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rank = new Rank();
        $form = $this->createForm(RankType::class, $rank);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rank);
            $entityManager->flush();
    
            $this->addFlash('success', 'Rank created successfully.');
    
            return $this->redirectToRoute('app_controller_rank_index', [], Response::HTTP_SEE_OTHER);
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

    #[Route('/{IDRang}/edit', name: 'app_controller_rank_edit')]
    public function edit(Request $request, Rank $rank, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RankType::class, $rank);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le rang a été mis à jour avec succès.');
            return $this->redirectToRoute('app_controller_rank_index');
        }

        return $this->render('rank/edit.html.twig', [
            'rank' => $rank,
            'form' => $form->createView(),
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

    // src/Controller/ControllerRankController.php

#[Route('/rank/export-pdf', name: 'app_controller_rank_export_pdf')]
public function exportRankPdf(Request $request, EntityManagerInterface $entityManager): Response
{
    // Get sorting and search parameters from request
    $sort = $request->query->get('sort');
    $searchTerm = $request->query->get('search');
    
    // Apply the same logic as your index action to get ranks
    $orderBy = [];
    if ($sort === 'asc') {
        $orderBy = ['points' => 'ASC'];
    } elseif ($sort === 'desc') {
        $orderBy = ['points' => 'DESC'];
    }

    $rankRepository = $entityManager->getRepository(Rank::class);
    if ($searchTerm) {
        $ranks = $rankRepository->createQueryBuilder('r')
            ->where('r.NomRank LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%')
            ->orderBy('r.points', $orderBy['points'] ?? 'ASC')
            ->getQuery()
            ->getResult();
    } else {
        $ranks = $rankRepository->findBy([], $orderBy);
    }

    // Get absolute file paths
    $projectDir = $this->getParameter('kernel.project_dir');
    $logoPath = $projectDir . '/public/images/logo.png';
    $signaturePath = $projectDir . '/public/images/signature.png';
    
    // Create new TCPDF document
    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Company');
    $pdf->SetTitle('Rank List');
    $pdf->SetSubject('List of all ranks with points');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Generate HTML content
    $html = $this->renderView('rank/pdf.html.twig', [
        'ranks' => $ranks,
        'sort' => $sort,
        'searchTerm' => $searchTerm,
        'logo_path' => $logoPath,
        'signature_path' => $signaturePath,
    ]);
    
    // Output the HTML content
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Close and output PDF document
    $pdfContent = $pdf->Output('ranks.pdf', 'S');
    
    return new Response(
        $pdfContent,
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rank-list-'.date('Y-m-d').'.pdf"',
        ]
    );
}
}