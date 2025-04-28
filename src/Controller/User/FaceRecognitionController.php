<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Service\FaceRecognitionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FaceRecognitionController extends AbstractController
{
    public function __construct(
        private FaceRecognitionService $faceRecognitionService,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private TokenStorageInterface $tokenStorage
    ) {}

    #[Route('/register-face', name: 'register_face', methods: ['POST'])]
    public function registerFace(Request $request): JsonResponse
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $image = $request->files->get('face_image');
        if (!$image) {
            return new JsonResponse(['error' => 'No image provided'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Detect faces in the image
            $faces = $this->faceRecognitionService->detectFaces($image);
            if (count($faces) !== 1) {
                return new JsonResponse(['error' => 'Please provide an image with exactly one face'], Response::HTTP_BAD_REQUEST);
            }

            // Create a face set for the user if they don't have one
            if (!$user->getFaceEmbedding()) {
                $faceSetToken = $this->faceRecognitionService->createFaceSet($user->getUserId());
                $user->setFaceEmbedding($faceSetToken);
            }

            // Add the face to the user's face set
            $this->faceRecognitionService->addFaceToSet($faces[0]['face_token'], $user->getFaceEmbedding());

            // Store the face image
            $user->setFaceImage(file_get_contents($image->getPathname()));

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Face registered successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/face-login', name: 'face_login', methods: ['GET', 'POST'])]
    public function loginFace(Request $request): Response
    {
        // If it's a GET request, display the face login page
        if ($request->isMethod('GET')) {
            return $this->render('security/face_login.html.twig');
        }

        // Handle POST request for face recognition
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Invalid request type'], Response::HTTP_BAD_REQUEST);
        }

        $image = $request->files->get('face_image');
        if (!$image) {
            return new JsonResponse(['error' => 'No image provided'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Get all users with face embeddings using DQL
            $users = $this->entityManager->createQueryBuilder()
                ->select('u')
                ->from(User::class, 'u')
                ->where('u.face_embedding IS NOT NULL')
                ->andWhere('u.isBanned = :isBanned')
                ->setParameter('isBanned', false)
                ->getQuery()
                ->getResult();

            if (empty($users)) {
                return new JsonResponse(['error' => 'No registered faces found in the system'], Response::HTTP_NOT_FOUND);
            }

            foreach ($users as $user) {
                try {
                    $results = $this->faceRecognitionService->searchFace($image, $user->getFaceEmbedding());
                    
                    // If we find a match with high confidence
                    if (!empty($results) && $results[0]['confidence'] > 80) {
                        // Create the token with the correct provider key
                        $token = new UsernamePasswordToken(
                            $user,
                            'main', // Firewall name
                            $user->getRoles()
                        );

                        // Set the token in the token storage
                        $this->tokenStorage->setToken($token);

                        // Get the session and store the token
                        $session = $request->getSession();
                        if ($session) {
                            $session->set('_security_main', serialize($token));
                        }

                        return new JsonResponse([
                            'success' => true,
                            'message' => 'Login successful',
                            'redirect' => $this->generateUrl('profile')
                        ]);
                    }
                } catch (\Exception $e) {
                    // Continue to next user if face search fails
                    continue;
                }
            }

            return new JsonResponse(['error' => 'No matching face found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Error during face recognition: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 