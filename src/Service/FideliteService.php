<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Fidelite;
use App\Entity\Rank;
use App\Repository\FideliteRepository;
use App\Repository\RankRepository;
use Doctrine\ORM\EntityManagerInterface;

class FideliteService
{
    private $entityManager;
    private $fideliteRepository;
    private $rankRepository;
    private $mailAPI;
    
    public function __construct(
        EntityManagerInterface $entityManager, 
        FideliteRepository $fideliteRepository,
        RankRepository $rankRepository,
        MailAPI $mailAPI
    ) {
        $this->entityManager = $entityManager;
        $this->fideliteRepository = $fideliteRepository;
        $this->rankRepository = $rankRepository;
        $this->mailAPI = $mailAPI;
    }
    
    public function getFideliteByUser(int $userId): ?Fidelite
    {
        return $this->fideliteRepository->findOneBy(['idUser' => $userId]);
    }
    
    public function updateFidelite(User $user): void
    {
        $oldFidelite = $this->getFideliteByUser($user->getUserId());
        $oldRank = $oldFidelite !== null ? $oldFidelite->getIdRank() : null;
    
        $fidelite = $this->calculateFidelite($user);
    
        if ($oldFidelite !== null) {
            $oldFidelite->setPoints($fidelite->getPoints());
            $oldFidelite->setRemise($fidelite->getRemise());
            $oldFidelite->setIdRank($fidelite->getIdRank());
    
            $this->entityManager->persist($oldFidelite);
            $this->entityManager->flush();
    
            if ($oldRank !== null && $oldRank !== $fidelite->getIdRank()) {
                $this->sendRankUpgradeEmail($user, $oldRank, $fidelite->getIdRank());
            }
        }
    }
    
public function addFidelite(User $user): void
{
    $fidelite = $this->calculateFidelite($user);
    $fidelite->setIdUser($user);
    
    $this->entityManager->persist($fidelite);
    $this->entityManager->flush();
}
    
    private function sendRankUpgradeEmail(User $user, Rank $oldRank, Rank $newRank): void
    {
        $this->mailAPI->sendEmail(
            $user->getEmail(),
            $user->getNom() . ' ' . $user->getPrenom(),
            $oldRank->getNomRank(),
            $newRank->getNomRank()
        );
    }
    
    public function calculateFidelite(User $user): Fidelite
{
    $points = (int)($user->getNbrVoyage() / 3);
    $remise = ($points / 3) * 1.5;

    $rankId = 1; 

    if ($points >= 20) {
        $rankId = 4;
    } elseif ($points >= 15) {
        $rankId = 3;
    } elseif ($points >= 10) {
        $rankId = 2;
    }

    $rank = $this->rankRepository->getRankById($rankId);

    if (!$rank) {
        throw new \RuntimeException("Rank with ID $rankId not found.");
    }

    $fidelite = new Fidelite();
    $fidelite->setPoints($points);
    $fidelite->setRemise($remise);
    $fidelite->setIdRank($rank);

    return $fidelite;
}
    
}
