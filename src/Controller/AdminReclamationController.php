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
use Symfony\Component\Validator\Validator\ValidatorInterface; // <-- à ajouter

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

    #[Route('/admin/reponse/{id}/edit', name: 'admin_reponse_edit', methods: ['GET', 'POST'])]
    public function editReponse(Request $request, Reponse $reponse, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $errors = $validator->validate($reponse);

            if (count($errors) > 0) {
                $this->addFlash('error', 'Erreur de validation dans le formulaire.');
            } else {
                $reponse->setDateRep(new \DateTime());
                $em->flush();

                $this->addFlash('success', 'Réponse modifiée avec succès');
                return $this->redirectToRoute('admin_reponses_list', ['id' => $reponse->getReclamation()->getId()]);
            }
        }

        return $this->render('reponse/edit_reponse.html.twig', [
            'form' => $form->createView(),
            'reponse' => $reponse
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
