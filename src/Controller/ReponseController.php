<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReponseController extends AbstractController
{
    private $entityManager;

    // Injecte le EntityManagerInterface dans le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/reclamation/{id}/reponse', name: 'app_reponse_new')]
    public function new(Request $request, ReclamationRepository $reclamationRepository, int $id): Response
    {
        $reclamation = $reclamationRepository->find($id);

        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found.');
        }

        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);
        $reponse->setDateRep(new \DateTime()); // Set the current date by default.

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($reponse);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_reponse_index');
        }

        return $this->render('admin/reponse_new.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/admin/reponses', name: 'app_reponse_index')]
    public function index(ReponseRepository $reponseRepository): Response
    {
        $reponses = $reponseRepository->findAll();

        return $this->render('admin/reponse_index.html.twig', [
            'reponses' => $reponses,
        ]);
    }

    #[Route('/admin/reponse/{id}/edit', name: 'app_reponse_edit')]
    public function edit(Request $request, Reponse $reponse): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_reponse_index');
        }

        return $this->render('admin/reponse_edit.html.twig', [
            'form' => $form->createView(),
            'reponse' => $reponse,
        ]);
    }

    #[Route('/admin/reponse/{id}/delete', name: 'app_reponse_delete')]
    public function delete(Reponse $reponse): Response
    {
        $this->entityManager->remove($reponse);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_reponse_index');
    }
}
