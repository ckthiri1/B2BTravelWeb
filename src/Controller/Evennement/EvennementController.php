<?php

namespace App\Controller\Evennement;

use App\Repository\EvennementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Evennement;
use App\Form\EvennementType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GeocodingService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class EvennementController extends AbstractController
{
    private HttpClientInterface $client;
    private string $huggingFaceToken;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->huggingFaceToken = $_ENV['HUGGING_FACE_TOKEN'] ?? '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'; 
    }

    #[Route('/evennements', name: 'app_evennement_index')]
    public function index(Request $request, EvennementRepository $evennementRepository, GeocodingService $geocodingService): Response
    {
        $search = $request->query->get('search');
        $evennements = $search
            ? $evennementRepository->findBySearch($search)
            : $evennementRepository->findAll();

        return $this->render('evennement/index.html.twig', [
            'evennements' => $evennements,
            'geocodingService' => $geocodingService,
        ]);
    }

    #[Route('/evennements/create', name: 'evennement_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $evennement = new Evennement();
        $form = $this->createForm(EvennementType::class, $evennement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($evennement);
            $em->flush();

            return $this->redirectToRoute('app_evennement_index');
        }

        return $this->render('evennement/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/evennements/{id}/edit', name: 'evennement_edit')]
    public function edit(Request $request, Evennement $evennement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EvennementType::class, $evennement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Évènement modifié avec succès.');
            return $this->redirectToRoute('app_evennement_index');
        }

        return $this->render('evennement/edit.html.twig', [
            'form' => $form->createView(),
            'evennement' => $evennement,
        ]);
    }

    #[Route('/evennements/{id}/delete', name: 'evennement_delete')]
    public function delete(Evennement $evennement, EntityManagerInterface $em): Response
    {
        $em->remove($evennement);
        $em->flush();
        $this->addFlash('success', 'Évènement supprimé avec succès.');
        return $this->redirectToRoute('app_evennement_index');
    }

    #[Route('/evennements/public', name: 'evennement_public')]
    public function publicIndex(EvennementRepository $evennementRepository): Response
    {
        $evennements = $evennementRepository->findAll();
        return $this->render('evennement/public.html.twig', [
            'evennements' => $evennements,
        ]);
    }

    #[Route('/evennements/{id}', name: 'evennement_show')]
    public function show(Evennement $evennement): Response
    {
        return $this->render('evennement/show.html.twig', [
            'evennement' => $evennement,
        ]);
    }

    #[Route('/evennements/{id}/export', name: 'evennement_export_signed_pdf', methods: ['POST'])]
    public function exportSignedPdf(Request $request, Evennement $evennement): Response
    {
        $signatureData = $request->request->get('signature');

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('evennement/pdf_signed.html.twig', [
            'evennement' => $evennement,
            'signature' => $signatureData,
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="evennement_' . $evennement->getId() . '.pdf"'
        ]);
    }

    #[Route('/generate-description', name: 'generate_event_description', methods: ['POST'])]
    public function generateDescription(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (empty($data['nomE']) || empty($data['dateE'])) {
            return new JsonResponse(['error' => 'Missing required fields: nomE or dateE.'], 400);
        }
    
        $nomE = $data['nomE'];
        $dateE = $data['dateE'];
    
        // More formal business-travel-related prompt
        $prompt = sprintf(
            "Rédige une courte description professionnelle en français (150 caractères) pour un événement d'affaires appelé '%s', prévu le %s. L'événement concerne un voyage professionnel, et la description doit être formelle, informative et adaptée à un public professionnel.",
            $nomE,
            $dateE
        );
    
        try {
            $response = $this->client->request('POST', 'https://api-inference.huggingface.co/models/mistralai/Mixtral-8x7B-Instruct-v0.1', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->huggingFaceToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_new_tokens' => 200,
                        'temperature' => 0.7,
                        'return_full_text' => true,
                        'wait_for_model' => true
                    ]
                ],
                'timeout' => 60
            ]);
    
            $content = $response->toArray(false);
    
            if (isset($content[0]['generated_text'])) {
                $generatedText = str_replace($prompt, '', $content[0]['generated_text']);
                return new JsonResponse(['description' => trim($generatedText)]);
            }
    
            return new JsonResponse(['error' => 'No generated text returned from API'], 500);
    
        } catch (\Exception $e) {
            error_log('[HuggingFace API Error] ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'API Error: ' . $e->getMessage(),
                'hint' => 'Check your API key, model availability, and response format.'
            ], 500);
        }
    }
       
}
