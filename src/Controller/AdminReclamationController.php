<?php

namespace App\Controller;

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
    
        // Analyse IA de la réclamation
        $aiAnalysis = $this->analyzeComplaint($reclamation->getDescription());
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reponse);
            $em->flush();
    
            $this->addFlash('success', 'Réponse enregistrée avec succès');
            return $this->redirectToRoute('admin_reponses_list', ['id' => $reclamation->getId()]);
        }
    
        return $this->render('reponse/repondre.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
            'ai_analysis' => $aiAnalysis // Passer l'analyse à la vue
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
    
    private function analyzeComplaint(string $text): array
    {
        $stopWords = ['le', 'la', 'les', 'un', 'une', 'des', 'de', 'du', 'au', 'aux', 
        'ce', 'cet', 'cette', 'ces', 'mon', 'ma', 'mes', 'ton', 'ta', 
        'tes', 'son', 'sa', 'ses', 'notre', 'votre', 'leur', 'je', 'tu',
        'il', 'nous', 'vous', 'ils', 'à', 'pour', 'avec', 'sans', 'dans',
        'sur', 'par', 'est', 'a', 'été', 'étais', 'étaient', 'avait', 'h'];

        // Détection de la catégorie
        $categories = [
            'Vol' => ['vol', 'compagnie', 'retard', 'annulé', 'bagage', 'décollage', 'embarquement'],
            'Hôtel' => ['hôtel', 'chambre', 'réservation', 'nettoyage', 'réception', 'room service'],
            'Transport' => ['taxi', 'navette', 'voiture', 'chauffeur', 'transfert', 'trajet'],
            'Visa' => ['visa', 'passeport', 'documents', 'ambassade', 'autorisation', 'tampon'],
            'Event' => ['événement', 'concert', 'conférence', 'réservation', 'billetterie', 'festival'],
            'Rank' => ['points', 'fidélité', 'statut', 'avantage', 'classement', 'niveau'],
            'Hebergement' => ['hébergement', 'logement', 'appartement', 'location', 'airbnb', 'chez lhabitant']
        ];
    
        $foundCategory = 'Autre';
        $maxMatches = 0;
    
        foreach ($categories as $category => $keywords) {
            $matchCount = 0;
            foreach ($keywords as $keyword) {
                if (preg_match("/\b" . preg_quote($keyword, '/') . "\b/i", $text)) {
                    $matchCount++;
                }
            }
            
            if ($matchCount > $maxMatches) {
                $maxMatches = $matchCount;
                $foundCategory = $category;
            }
        }
    
        $priority = 1;
        $priorityTerms = [
            'urgent' => 2,
            'asap' => 3,
            'critique' => 3,
            'annulé' => 4,
            'erreur' => 2,
            'impossible' => 3,
            'bloqué' => 3
        ];
    
        foreach ($priorityTerms as $term => $value) {
            if (stripos($text, $term) !== false) $priority += $value;
        }
        $priority = min($priority, 5);
    
        // Suggestions de réponses
        $suggestions = [
            'Vol' => [
                "Nous priorisons votre dossier voyage : réorganisation immédiate des vols",
                "Compensation proposée :\n- Remboursement de 200€\n- Upgrade classe affaires sur votre prochain vol"
            ],
            'Hôtel' => [
                "Solution d'hébergement alternative :\n- Transfert vers un hôtel 4 étoiles partenaire\n- Service de conciergerie 24h/24"
            ],
            'Event' => [
                "Pour cet événement :\n- Accès VIP garanti\n- Rencontre privilégiée avec les organisateurs",
                "Dédommagement exceptionnel :\n- Places Gold offertes pour la prochaine édition\n- Pack expérience backstage"
            ],
            'Rank' => [
                "Maintien de vos avantages :\n- Crédit de 5000 points fidélité\n- Extension de validité 6 mois",
                "Compensation statut :\n- Accès lounge premium\n- Service prioritaire dédié"
            ],
            'Hebergement' => [
                "Solution immédiate :\n- Relogement en suite exécutive\n- Service de ménage express",
                "Avantages compensatoires :\n- Nuitée offerte\n- Forfait spa illimité"
            ],
            'Transport' => [
                "Réorganisation :\n- Véhicule de remplacement avec chauffeur\n- Prise en charge des frais supplémentaires"
            ],
            'Visa' => [
                "Assistance urgente :\n- Consultation avec notre expert visas\n- Prise de rendez-vous express à l'ambassade"
            ]
        ];
        
        return [
            'category' => $foundCategory,
            'priority' => $priority,
            'keywords' => array_unique(array_merge(
                explode(' ', preg_replace('/[^a-zÀ-ÿ ]/i', '', strtolower($text))),
                $categories[$foundCategory] ?? []
            )),
            'suggestions' => $suggestions[$foundCategory] ?? ["Nous traitons votre demande avec la plus haute priorité."]
        ];
        
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
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
public function dashboard(EntityManagerInterface $em): Response
{
    $reclamationRepo = $em->getRepository(Reclamation::class);
    $reponseRepo = $em->getRepository(Reponse::class);

    return $this->render('reponse/admin_dashboard.html.twig', [
        'stats' => [
            'total_reclamations' => $reclamationRepo->count([]),
            'pending_reclamations' => $reclamationRepo->count(['status' => 'pending']),
            'resolved_reclamations' => $reclamationRepo->count(['status' => 'resolved']),
            'total_reponses' => $reponseRepo->count([]),
            'reponses_recentes' => $reponseRepo->countLastWeekResponses()
        ],
        'last_reponses' => $reponseRepo->findBy([], ['dateRep' => 'DESC'], 5),
        'pending_claims' => $reclamationRepo->count(['status' => 'pending'])
    ]);
    
}
#[Route('/admin/reclamation/{id}/inserer', name: 'admin_inserer_suggestion', methods: ['POST'])]
public function insererSuggestion(Request $request, Reclamation $reclamation, EntityManagerInterface $em): Response
{
    $suggestion = $request->request->get('suggestion');

    $reponse = new Reponse();
    $reponse->setDescriptionRep($suggestion);
    $reponse->setReclamation($reclamation);
    $reponse->setDateRep(new \DateTime());

    $em->persist($reponse);
    $em->flush();

    $this->addFlash('success', 'Suggestion insérée avec succès');
    return $this->redirectToRoute('admin_reponses_list', ['id' => $reclamation->getId()]);
}
}