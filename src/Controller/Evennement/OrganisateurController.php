<?php

namespace App\Controller\Evennement;

use App\Entity\Organisateur;
use App\Form\OrganisateurType;
use App\Repository\OrganisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\TwilioService;


class OrganisateurController extends AbstractController
{

    private TwilioService $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }
    #[Route('/organisateur', name: 'organisateur_index')]
    public function index(OrganisateurRepository $organisateurRepository): Response
    {
        $organisateurs = $organisateurRepository->findAll();
        return $this->render('organisateur/index.html.twig', [
            'organisateurs' => $organisateurs,
        ]);
        
    }

    #[Route('/organisateur/create', name: 'organisateur_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $organisateur = new Organisateur();
        $form = $this->createForm(OrganisateurType::class, $organisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($organisateur);
            $entityManager->flush();
            $this->twilio->sendSms(
                $organisateur->getContact(),
                "Bonjour {$organisateur->getNomOr()}, vous avez été ajouté comme organisateur sur B2B Travel."
            );
            

            return $this->redirectToRoute('organisateur_index');
        }

        return $this->render('organisateur/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/organisateur/{id}', name: 'organisateur_show')]
    public function show(Organisateur $organisateur): Response
    {
        return $this->render('organisateur/show.html.twig', [
            'organisateur' => $organisateur,
        ]);
    }

    #[Route('/organisateur/{id}/edit', name: 'organisateur_edit')]
public function edit(Request $request, Organisateur $organisateur, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(OrganisateurType::class, $organisateur);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        return $this->redirectToRoute('organisateur_index');
    }

    return $this->render('organisateur/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/organisateur/{id}/delete', name: 'organisateur_delete')]
    public function delete(Organisateur $organisateur, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($organisateur);
        $entityManager->flush();

        return $this->redirectToRoute('organisateur_index');
    }
}
