<?php
// src/Repository/HebergementRepository.php

namespace App\Repository;

use App\Entity\Hebergement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HebergementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hebergement::class);
    }
    public function getCountByType(): array
    {
        return $this->createQueryBuilder('h')
            ->select('h.type as type, COUNT(h.id) as count') // Plus de jointure
            ->groupBy('h.type')
            ->getQuery()
            ->getResult();
    }
    
    public function getAveragePriceByType(): array
    {
        return $this->createQueryBuilder('h')
            ->select('h.type as type, AVG(h.prix) as average_price') // Plus de jointure
            ->groupBy('h.type')
            ->getQuery()
            ->getResult();
    }

public function getPriceStatistics(): array
{
    return $this->createQueryBuilder('h')
        ->select(
            'MIN(h.prix) as min_price',
            'MAX(h.prix) as max_price',
            'AVG(h.prix) as avg_price'
        )
        ->getQuery()
        ->getSingleResult();
}
public function search(
    ?string $type,
    ?string $sortPrice,
    ?string $address,
    ?string $searchTerm
): array {
    $qb = $this->createQueryBuilder('h');

    if ($type) {
        $qb->andWhere('h.type = :type')
           ->setParameter('type', $type);
    }

    if ($sortPrice) {
        $qb->orderBy('h.prix', strtoupper($sortPrice)); // Conversion en majuscules
    }

    if ($address) {
        $qb->andWhere('h.adresse LIKE :address')
           ->setParameter('address', '%' . $address . '%');
    }

    if ($searchTerm) {
        $qb->andWhere('h.nom LIKE :searchTerm')
           ->setParameter('searchTerm', '%' . $searchTerm . '%');
    }

    return $qb->getQuery()->getResult();
}
}