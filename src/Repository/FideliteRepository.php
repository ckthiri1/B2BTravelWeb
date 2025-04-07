<?php

namespace App\Repository;

use App\Entity\Fidelite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fidelite>
 */
class FideliteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fidelite::class);
    }
    
    /**
     * Find a fidelite record by user ID
     */
    public function findOneByUser(int $userId): ?Fidelite
    {
        return $this->createQueryBuilder('f')
            ->join('f.user', 'u')
            ->where('u.user_id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function getFideliteWithRankByUser(int $userId): ?Fidelite
    {
        $results = $this->createQueryBuilder('f')
            ->leftJoin('f.IdRank', 'r')
            ->addSelect('r')
            ->where('f.idUser = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    
        return $results[0] ?? null; // return first one safely
    }
    
    
}