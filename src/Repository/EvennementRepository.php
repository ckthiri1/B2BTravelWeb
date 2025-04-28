<?php

namespace App\Repository;

use App\Entity\Evennement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvennementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evennement::class);
    }

    public function findTopOrganisateur(): ?array
    {
        return $this->createQueryBuilder('e')
            ->select('o.nomOr, COUNT(e.id) AS eventCount')
            ->join('e.IdOr', 'o') // ✅ Utilisation correcte de l'association (majuscule "I")
            ->groupBy('o.idor')   // ✅ correspond à la colonne en base
            ->orderBy('eventCount', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countEventsLast6Months(): array
{
    $qb = $this->createQueryBuilder('e')
        ->select("DATE_FORMAT(e.dateE, '%Y-%m') AS month", "COUNT(e.id) AS count")
        ->where("e.dateE >= :date")
        ->setParameter('date', new \DateTime('-6 months'))
        ->groupBy('month')
        ->orderBy('month', 'ASC');

    return $qb->getQuery()->getResult();
}
public function findNextEvent(): ?Evennement
{
    return $this->createQueryBuilder('e')
        ->where('e.DateE > :now') // ✅ Bonne casse, comme l'entité
        ->setParameter('now', new \DateTime())
        ->orderBy('e.DateE', 'ASC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

public function findBySearch(string $term): array
{
    return $this->createQueryBuilder('e')
        ->where('LOWER(e.NomE) LIKE :term')
        ->setParameter('term', '%' . strtolower($term) . '%')
        ->orderBy('e.DateE', 'ASC')
        ->getQuery()
        ->getResult();
}




}
