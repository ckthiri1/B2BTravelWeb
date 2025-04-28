<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\TranslationService;

class TranslationController extends AbstractController
{
    #[Route('/api/translate', name: 'api_translate', methods: ['POST'])]
    public function translate(Request $request, TranslationService $translationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $text = $data['text'] ?? '';
        $targetLanguage = $data['target'] ?? 'fr';
        $sourceLanguage = $data['source'] ?? 'en';

        if (empty($text)) {
            return $this->json(['error' => 'No text provided'], 400);
        }

        $translatedText = $translationService->translate($text, $targetLanguage, $sourceLanguage);

        return $this->json([
            'translatedText' => $translatedText,
            'sourceLanguage' => $sourceLanguage,
            'targetLanguage' => $targetLanguage
        ]);
    }

    #[Route('/api/languages', name: 'api_languages', methods: ['GET'])]
    public function getLanguages(TranslationService $translationService): JsonResponse
    {
        $languages = $translationService->getSupportedLanguages();
        return $this->json($languages);
    }

    #[Route('/api/translate-page', name: 'api_translate_page', methods: ['POST'])]
    public function translatePage(Request $request, TranslationService $translationService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $html = $data['html'] ?? '';
        $targetLanguage = $data['target'] ?? 'fr';
        $sourceLanguage = $data['source'] ?? 'en';

        if (empty($html)) {
            return $this->json(['error' => 'No HTML content provided'], 400);
        }

        // Extract text nodes from HTML (simplified example)
        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        
        $xpath = new \DOMXPath($dom);
        $textNodes = $xpath->query('//text()[not(ancestor::script) and not(ancestor::style)]');
        
        foreach ($textNodes as $node) {
            $text = trim($node->nodeValue);
            if (!empty($text)) {
                $translated = $translationService->translate($text, $targetLanguage, $sourceLanguage);
                $node->nodeValue = $translated;
            }
        }

        $translatedHtml = $dom->saveHTML();
        
        return $this->json([
            'translatedHtml' => $translatedHtml,
            'sourceLanguage' => $sourceLanguage,
            'targetLanguage' => $targetLanguage
        ]);
    }
}
?>