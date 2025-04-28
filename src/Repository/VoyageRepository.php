<?php

namespace App\Repository;

use App\Entity\Voyage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VoyageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voyage::class);
    }

    public function findByDepartAndDestination(string $depart, string $destination)
    {
        return $this->createQueryBuilder('v')
            ->where('v.depart = :depart')
            ->andWhere('v.Destination = :destination')
            ->setParameter('depart', $depart)
            ->setParameter('destination', $destination)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
