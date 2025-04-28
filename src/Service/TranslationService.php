<?php
// src/Service/TranslationService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class TranslationService
{
    private $httpClient;
    private $cache;
    private $translationApis = [
        'libretranslate' => 'https://libretranslate.de/translate',
        'mymemory' => 'https://api.mymemory.translated.net/get'
    ];

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->cache = new FilesystemAdapter();
    }

    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
    {
        // Skip translation if source and target are same
        if ($sourceLanguage === $targetLanguage) {
            return $text;
        }

        // Create a cache key based on the text and languages
        $cacheKey = md5("trans_{$sourceLanguage}_{$targetLanguage}_".urlencode($text));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($text, $targetLanguage, $sourceLanguage) {
            $item->expiresAfter(86400); // Cache for 24 hours
            
            // Try LibreTranslate first
            $translated = $this->tryLibreTranslate($text, $targetLanguage, $sourceLanguage);
            if ($translated !== $text) {
                return $translated;
            }

            // Fallback to MyMemory if LibreTranslate fails
            return $this->tryMyMemoryTranslate($text, $targetLanguage, $sourceLanguage);
        });
    }

    private function tryLibreTranslate(string $text, string $target, string $source): string
    {
        try {
            $response = $this->httpClient->request('POST', $this->translationApis['libretranslate'], [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'q' => $text,
                    'source' => $source,
                    'target' => $target,
                    'format' => 'text'
                ],
                'timeout' => 3
            ]);

            $data = $response->toArray();
            return $data['translatedText'] ?? $text;
        } catch (\Exception $e) {
            return $text;
        }
    }

    private function tryMyMemoryTranslate(string $text, string $target, string $source): string
    {
        try {
            $response = $this->httpClient->request('GET', $this->translationApis['mymemory'], [
                'query' => [
                    'q' => $text,
                    'langpair' => "{$source}|{$target}",
                    'de' => 'ghassenhachaichi2@email.com' 
                ],
                'timeout' => 3
            ]);

            $data = $response->toArray();
            return $data['responseData']['translatedText'] ?? $text;
        } catch (\Exception $e) {
            return $text;
        }
    }

    public function getSupportedLanguages(): array
    {
        return [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'it', 'name' => 'Italian'],
            ['code' => 'ar', 'name' => 'Arabic'],
            ['code' => 'zh', 'name' => 'Chinese'],
            ['code' => 'ja', 'name' => 'Japanese'],
            ['code' => 'ru', 'name' => 'Russian']
        ];
    }
}
?>