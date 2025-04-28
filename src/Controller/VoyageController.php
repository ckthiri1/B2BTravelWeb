<?php

namespace App\Controller;

use App\Entity\Vol;
use App\Entity\Voyage;
use App\Form\VoyageType;
use App\Service\VoyageDescriptionIA;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/voyage')]
final class VoyageController extends AbstractController
{
    private VoyageDescriptionIA $voyageDescriptionIA;

    public function __construct(VoyageDescriptionIA $voyageDescriptionIA)
    {
        $this->voyageDescriptionIA = $voyageDescriptionIA;
    }
    
    #[Route('/generate-description', name: 'generate_description', methods: ['POST'])]
    public function generateDescription(
        Request $request, 
        CsrfTokenManagerInterface $csrfTokenManager,
        LoggerInterface $logger
    ): JsonResponse {
        $logger->info('Generate description endpoint hit');
        
        // Get the CSRF token from the request headers
        $submittedToken = $request->headers->get('X-CSRF-TOKEN');
        $logger->debug('CSRF token received', ['token' => $submittedToken]);
    
        // Verify the CSRF token
        if (!$csrfTokenManager->isTokenValid($csrfTokenManager->getToken('generate_description'), $submittedToken)) {
            $logger->error('Invalid CSRF token');
            return new JsonResponse(['error' => 'Invalid CSRF token'], 403);
        }
    
        $data = json_decode($request->getContent(), true);
        $logger->debug('Request data received', ['data' => $data]);
        
        if (!$data || !isset($data['departure']) || !isset($data['arrival'])) {
            $logger->error('Invalid data received');
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }
        
        $departure = $data['departure'];
        $arrival = $data['arrival'];
        $logger->info('Generating description for route', ['departure' => $departure, 'arrival' => $arrival]);
    
        try {
            $description = $this->voyageDescriptionIA->generateDescription($departure, $arrival);
            $logger->info('Description generated successfully');
            
            return new JsonResponse(['description' => $description]);
        } catch (\Exception $e) {
            $logger->error('Error generating description', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse(['error' => 'Erreur lors de la génération de la description: ' . $e->getMessage()], 500);
        }
    }
     

    #[Route('/count', name: 'app_voyage_count', methods: ['GET'])]
    public function countVoyages(EntityManagerInterface $entityManager): JsonResponse
    {
        $voyageCount = $entityManager->getRepository(Voyage::class)->count([]);
        
        return new JsonResponse(['voyageCount' => $voyageCount]);
    }
    

    #[Route('/ListVoyage', name: 'app_voyage_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $voyages = $entityManager
            ->getRepository(Voyage::class)
            ->findAll();

        return $this->render('voyage/ListVoyage.twig', [
            'voyages' => $voyages,
        ]);

    }

 #[Route('/new', name: 'app_voyage_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $voyage = new Voyage();
    $form = $this->createForm(VoyageType::class, $voyage);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Check if the voyage already exists
        $existingVoyage = $entityManager->getRepository(Voyage::class)
            ->findOneBy(['depart' => $voyage->getDepart(), 'Destination' => $voyage->getDestination()]);

        if ($existingVoyage) {
            $this->addFlash('error', 'Ce voyage existe déjà dans la base de données.');
        } else {
            $entityManager->persist($voyage);
            $entityManager->flush();
            $this->addFlash('success', 'Voyage ajouté avec succès !');
            return $this->redirectToRoute('app_voyage_index');
        }
    }

    return $this->render('voyage/CreateVoyage.twig', [
        'voyage' => $voyage,
        'form' => $form->createView(),
    ]);
}
    
    #[Route('/{VID}', name: 'app_voyage_show', methods: ['GET'])]
    public function show(Voyage $voyage): Response
    {
        return $this->render('voyage/show.html.twig', [
            'voyage' => $voyage,
        ]);
    }

    #[Route('/update/{id}', name: 'app_voyage_update', methods: ['GET', 'POST'])]
    public function updateVoyage(
        Request $request, 
        Voyage $voyage, 
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(VoyageType::class, $voyage);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Ensure fields are not null
            if ($voyage->getDepart() === null) {
                $voyage->setDepart('');
            }
            
            if ($voyage->getDestination() === null) {
                $voyage->setDestination('');
            }
            
            if ($voyage->getDescription() === null) {
                $voyage->setDescription('');
            }
            
            if ($form->isValid()) {
                // Check if voyage already exists
                $existingVoyage = $entityManager->getRepository(Voyage::class)
                    ->findOneBy([
                        'depart' => $voyage->getDepart(),
                        'Destination' => $voyage->getDestination()
                    ]);

                if ($existingVoyage && $existingVoyage->getVid() !== $voyage->getVid()) {
                    $this->addFlash('error', 'Ce voyage avec le même départ et la même destination existe déjà.');
                    return $this->render('voyage/UpdateVoyage.twig', [
                        'form' => $form->createView(),
                        'voyage' => $voyage
                    ]);
                }

                $entityManager->flush();

                $this->addFlash('success', 'Le voyage a été mis à jour avec succès.');
                return $this->redirectToRoute('app_voyage_index');
            }
        }

        return $this->render('voyage/UpdateVoyage.twig', [
            'form' => $form->createView(),
            'voyage' => $voyage
        ]);
    }

    #[Route('/{VID}', name: 'app_voyage_delete', methods: ['POST'])]
    public function delete(Request $request, Voyage $voyage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$voyage->getVID(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($voyage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_voyage_index', [], Response::HTTP_SEE_OTHER);
    }

}
