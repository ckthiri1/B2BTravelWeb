<?php

namespace App\Controller\Reclamation;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReponseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TCPDF;

class AdminReclamationController extends AbstractController
{
    #[Route('/admin/reclamations', name: 'admin_reclamations')]
    public function list(EntityManagerInterface $em): Response
    {
        $reclamations = $em->getRepository(Reclamation::class)->findAll();
        
        return $this->render('reponse/reclamation_list.html.twig', [
            'reclamations' => $reclamations
        ]);
    }

    #[Route('/admin/reclamation/{id}/repondre', name: 'admin_reclamation_repondre', methods: ['GET', 'POST'])]
    public function repondre(Request $request, Reclamation $reclamation, EntityManagerInterface $em): Response
    {
        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);
        $reponse->setDateRep(new \DateTime());
    
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reponse);
            $em->flush();
    
            $this->addFlash('success', 'Réponse enregistrée avec succès');
            return $this->redirectToRoute('admin_reponses_list', ['id' => $reclamation->getId()]);
        }
    
        // Si le formulaire est soumis mais non valide, les erreurs seront automatiquement gérées par Symfony
        return $this->render('reponse/repondre.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView()
        ]);
    }
    

    #[Route('/admin/reclamation/{id}/reponses', name: 'admin_reponses_list', methods: ['GET', 'POST'])]
    public function listReponses(Request $request, Reclamation $reclamation, EntityManagerInterface $em): Response
    {
        $search = $request->query->get('search');

        $reponseRepo = $em->getRepository(Reponse::class);

        $queryBuilder = $reponseRepo->createQueryBuilder('r')
            ->where('r.reclamation = :reclamation')
            ->setParameter('reclamation', $reclamation);

        if ($search) {
            $queryBuilder->andWhere('r.descriptionRep LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        $reponses = $queryBuilder->getQuery()->getResult();

        return $this->render('reponse/list_reponses.html.twig', [
            'reclamation' => $reclamation,
            'reponses' => $reponses,
            'search' => $search
        ]);
    }


   #[Route('/admin/reclamation/{id}/export-pdf', name: 'admin_reclamation_export_pdf')]
public function exportPdf(Reclamation $reclamation, EntityManagerInterface $em): Response
{
    $reponses = $em->getRepository(Reponse::class)
        ->findBy(['reclamation' => $reclamation]);

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Configuration du document
    $pdf->SetCreator('Système de Gestion des Réclamations');
    $pdf->SetAuthor('Administrateur');
    $pdf->SetTitle('Réponses - Réclamation #'.$reclamation->getId());
    $pdf->SetMargins(15, 25, 15); // Marge supérieure augmentée pour le logo
    $pdf->AddPage();

    // Chemin absolu vers l'image
    $logoPath = $this->getParameter('kernel.project_dir').'/public/images/logo.png';

    // Génération du contenu HTML
    $html = $this->renderView('reponse/export_pdf.html.twig', [
        'reclamation' => $reclamation,
        'reponses' => $reponses,
        'logo_path' => $logoPath // Passer le chemin au template
    ]);

    $pdf->writeHTML($html, true, false, true, false, '');

    return new Response(
        $pdf->Output('reponses_reclamation_'.$reclamation->getId().'.pdf', 'I'),
        200,
        ['Content-Type' => 'application/pdf']
    );
}
#[Route('/admin/reponse/{id}/edit', name: 'admin_reponse_edit', methods: ['GET', 'POST'])]
public function editReponse(Request $request, Reponse $reponse, EntityManagerInterface $em): Response
{
    $form = $this->createForm(ReponseType::class, $reponse);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            $reponse->setDateRep(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Réponse modifiée avec succès');
            return $this->redirectToRoute('admin_reponses_list', ['id' => $reponse->getReclamation()->getId()]);
        }
        // Si le formulaire est invalide, on reste sur la même page
        $this->addFlash('error', 'Erreur de validation dans le formulaire');
    }

    return $this->render('reponse/edit_reponse.html.twig', [
        'form' => $form->createView(),
        'reponse' => $reponse,
        'reclamation' => $reponse->getReclamation() // Important pour afficher les détails
    ]);
}

    #[Route('/admin/reponse/{id}', name: 'admin_reponse_delete', methods: ['POST'])]
    public function deleteReponse(Request $request, Reponse $reponse, EntityManagerInterface $em): Response
    {
        $reclamationId = $reponse->getReclamation()->getId();
        
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->request->get('_token'))) {
            $em->remove($reponse);
            $em->flush();
            
            $this->addFlash('success', 'Réponse supprimée avec succès');
        }

        return $this->redirectToRoute('admin_reponses_list', ['id' => $reclamationId]);
    }
}