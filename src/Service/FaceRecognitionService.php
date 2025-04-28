<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FaceRecognitionService
{
    private string $apiKey;
    private string $apiSecret;
    private string $apiEndpoint;

    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->apiEndpoint = 'https://api-us.faceplusplus.com/facepp/v3';
    }

    /**
     * Detect faces in an image and return face tokens
     */
    public function detectFaces(UploadedFile $image): array
    {
        $client = HttpClient::create();
        
        try {
            $response = $client->request('POST', $this->apiEndpoint . '/detect', [
                'body' => [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'image_file' => fopen($image->getPathname(), 'r'),
                ],
            ]);

            $data = json_decode($response->getContent(), true);
            
            if (!isset($data['faces']) || empty($data['faces'])) {
                throw new \Exception('No faces detected in the image');
            }

            return $data['faces'];
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Failed to connect to Face++ API: ' . $e->getMessage());
        }
    }

    /**
     * Create a face set for a user
     */
    public function createFaceSet(string $userId): string
    {
        $client = HttpClient::create();
        
        try {
            $response = $client->request('POST', $this->apiEndpoint . '/faceset/create', [
                'body' => [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'display_name' => 'user_' . $userId,
                    'outer_id' => 'user_' . $userId,
                ],
            ]);

            $data = json_decode($response->getContent(), true);
            
            if (!isset($data['faceset_token'])) {
                throw new \Exception('Failed to create face set');
            }

            return $data['faceset_token'];
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Failed to connect to Face++ API: ' . $e->getMessage());
        }
    }

    /**
     * Add a face to a user's face set
     */
    public function addFaceToSet(string $faceToken, string $faceSetToken): void
    {
        $client = HttpClient::create();
        
        try {
            $response = $client->request('POST', $this->apiEndpoint . '/faceset/addface', [
                'body' => [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'faceset_token' => $faceSetToken,
                    'face_tokens' => $faceToken,
                ],
            ]);

            $data = json_decode($response->getContent(), true);
            
            if (!isset($data['faceset_token'])) {
                throw new \Exception('Failed to add face to set');
            }
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Failed to connect to Face++ API: ' . $e->getMessage());
        }
    }

    /**
     * Search for a face in a face set
     */
    public function searchFace(UploadedFile $image, string $faceSetToken): array
    {
        $client = HttpClient::create();
        
        try {
            $response = $client->request('POST', $this->apiEndpoint . '/search', [
                'body' => [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret,
                    'image_file' => fopen($image->getPathname(), 'r'),
                    'faceset_token' => $faceSetToken,
                ],
            ]);

            $data = json_decode($response->getContent(), true);
            
            if (!isset($data['results']) || empty($data['results'])) {
                throw new \Exception('No matching faces found');
            }

            return $data['results'];
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Failed to connect to Face++ API: ' . $e->getMessage());
        }
    }
} 