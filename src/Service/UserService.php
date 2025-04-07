<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $entityManager;
    private $userRepository;
    
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }
    
    public function getUserById(int $userId): ?User
    {
        return $this->userRepository->find($userId);
    }
    
    public function updateUser(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}