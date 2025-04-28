<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;
use App\Service\SmsNotifier;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{

    private SmsNotifier $twilio;
    

    public function __construct(SmsNotifier $twilio)
    {
$this->twilio = $twilio;
    }









    #[Route('/ReclamationList', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reclamations = $entityManager
            ->getRepository(Reclamation::class)
            ->findAll();

        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/reclamation/new', name: 'app_reclamation_new')]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        
    ): Response {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            $errors = $validator->validate($reclamation);
            
            if (count($errors) === 0) {
                $entityManager->persist($reclamation);
                $entityManager->flush();
                $this->twilio->sendSms("+21627185228","🚨 Nouvelle réclamation client !");

                

                
                $this->addFlash('success', '🚨 Nouvelle réclamation client !');
                return $this->redirectToRoute('app_reclamation_index');
            }
    
            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
    
        return $this->render('reclamation/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

#[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(ReclamationType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_reclamation_index');
    }

    return $this->render('reclamation/edit.html.twig', [
        'form' => $form->createView(),
        'reclamation' => $reclamation,
    ]);
}


    
    #[Route('/delete/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);
        
        if (!$reclamation) {
            throw $this->createNotFoundException();
        }
    
        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
            $this->addFlash('success', 'Reclamation deleted successfully!');
        }
    
        return $this->redirectToRoute('app_reclamation_index');
    }
    #[Route('/{id}/voir-reponse', name: 'app_reclamation_reponse_show', methods: ['GET'])]
    public function voirReponse(int $id, EntityManagerInterface $em): Response
    {
        $reclamation = $em->getRepository(Reclamation::class)->find($id);
    
        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation introuvable.');
        }
    
        $reponse = $reclamation->getLatestReponse();
    
        if (!$reponse) {
            throw $this->createNotFoundException('Aucune réponse trouvée pour cette réclamation.');
        }
    
        return $this->render('reclamation/reponse_show.html.twig', [
            'reclamation' => $reclamation,
            'reponse' => $reponse,
        ]);
    }
}