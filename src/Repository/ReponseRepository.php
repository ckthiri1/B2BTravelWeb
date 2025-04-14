<?php

namespace App\Repository;

use App\Entity\Reponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reponse>
 */
class ReponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reponse::class);
    }

    //    /**
    //     * @return Reponse[] Returns an array of Reponse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reponse
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    // src/Repository/ReponseRepository.php

    public function findRecentResponses(int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.dateRep', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    // src/Repository/ReponseRepository.php

public function countLastWeekResponses(): int
{
    return $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->where('r.dateRep >= :last_week')
        ->setParameter('last_week', new \DateTime('-7 days'))
        ->getQuery()
        ->getSingleScalarResult();
}
}
