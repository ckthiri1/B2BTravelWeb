<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Vol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vol::class);
    }

    public function findLastFlightId()
    {
        return $this->createQueryBuilder('v')
            ->select('v.volID')
            ->orderBy('v.volID', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findNonReservedByUser(User $user): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.user = :user')
            ->andWhere('v.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'NON_RESERVER')
            ->orderBy('v.dateDepart', 'DESC')
            ->getQuery()
            ->getResult();
    }
}