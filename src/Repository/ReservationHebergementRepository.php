<?php

namespace App\Repository;

use App\Entity\ReservationHebergement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationHebergementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationHebergement::class);
    }

    public function search(
        ?string $status,
        ?string $type, 
        ?string $search, 
        ?string $sortPrice,
        ?string $sortDate,
        ?string $sortName = null
    ): array {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.hebergement', 'h');
    
        // Filtres existants
        if ($status) {
            $qb->andWhere('r.status = :status')
                ->setParameter('status', $status);
        }
    
        if ($type) {
            $qb->andWhere('h.type = :type')
                ->setParameter('type', $type);
        }
    
        // Modification de la partie recherche
       
    // CORRECTION ICI : Recherche optimisée
    if ($search) {
        $orX = $qb->expr()->orX()
            ->add($qb->expr()->like('h.nom', ':search'));

        if (is_numeric($search)) {
            $orX->add($qb->expr()->eq('r.idResH', ':searchId'));
            $qb->setParameter('searchId', (int)$search);
        }

        $qb->andWhere($orX)
           ->setParameter('search', '%' . $search . '%');
    }

    if ($sortPrice) {
        $qb->addOrderBy('r.prix', $sortPrice === 'asc' ? 'ASC' : 'DESC');
    }

    if ($sortDate) {
        $qb->addOrderBy('r.date', $sortDate === 'asc' ? 'ASC' : 'DESC');
    }

    if ($sortName) {
        $qb->orderBy('h.nom', $sortName === 'asc' ? 'ASC' : 'DESC');
    }

    return $qb->getQuery()->getResult();
}
    
}