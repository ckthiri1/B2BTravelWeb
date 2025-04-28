<?php

namespace App\Repository;

use App\Entity\Reservation_voyage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class Reservation_voyageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation_voyage::class);
    }

    public function findAllWithDetails()
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.id_user', 'u')
            ->addSelect('u')
            ->leftJoin('r.id_vol', 'v')
            ->addSelect('v')
            ->leftJoin('v.idVoyage', 'voyage')
            ->addSelect('voyage')
            ->orderBy('r.id_resV', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
?>