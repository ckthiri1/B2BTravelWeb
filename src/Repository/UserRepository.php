<?php 

namespace App\Repository;

use App\Entity\User;
use App\Entity\Vol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Find all flights for a specific user
     */
    public function findUserFlights(User $user): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vol::class, 'v')
            ->where('v.user = :user')
            ->setParameter('user', $user)
            ->orderBy('v.dateDepart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find non-reserved flights for a specific user
     */
    public function findNonReservedFlights(User $user): array
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('v')
            ->from(Vol::class, 'v')
            ->where('v.user = :user')
            ->andWhere('v.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'NON_RESERVER')
            ->orderBy('v.dateDepart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count all users in the system
     */
    public function countAllUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.userId)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count banned users
     */
    public function countBannedUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.userId)')
            ->where('u.isBanned = :isBanned')
            ->setParameter('isBanned', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count unbanned users
     */
    public function countUnbannedUsers(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.userId)')
            ->where('u.isBanned = :isBanned')
            ->setParameter('isBanned', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get a fixed number of users from last month
     * This is a placeholder - in a real application, you might want to store
     * monthly user counts in a separate table
     */
    public function countUsersLastMonth(): int
    {
        // For demonstration, we'll return 80% of the current count
        // In a real application, you should store and retrieve actual monthly counts
        return (int) ($this->countAllUsers() * 0.8);
    }

    /**
     * Find a user by their ID
     */
    public function findUserById(int $userId): ?User
    {
        return $this->find($userId);
    }

    public function search(string $query): array
{
    $qb = $this->createQueryBuilder('u');
    $searchTerm = '%'.strtolower($query).'%';

    return $qb
        ->where(
            $qb->expr()->orX(
                $qb->expr()->like('LOWER(u.prenom)', ':query'),
                $qb->expr()->like('LOWER(u.nom)', ':query'),
                $qb->expr()->like('LOWER(u.email)', ':query'),
                $qb->expr()->like('LOWER(u.role)', ':query')
            )
        )
        ->setParameter('query', $searchTerm)
        ->orderBy('u.userId', 'DESC')
        ->getQuery()
        ->getResult();
}
}