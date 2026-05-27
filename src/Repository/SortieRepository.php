<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findFuturSortiesByOrganisateur($organisateurId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.organisateur = :org')
            ->andWhere('s.dateHeureDebut > :now')
            ->setParameter('org', $organisateurId)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByOrganisateur($organisateurId)
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.organisateur = :org')
            ->setParameter('org', $organisateurId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countFuturSorties(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.dateHeureDebut > :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPastSorties(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.dateHeureDebut < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countCancelledSorties(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.motifAnnulation IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPastByParticipant(int $userId): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(DISTINCT s.id)')
            ->join('s.participants', 'p')
            ->andWhere('p.id = :userId')
            ->andWhere('s.dateHeureDebut < :now')
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countUpcomingByUser(int $userId): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(DISTINCT s.id)')
            ->leftJoin('s.participants', 'p')
            ->andWhere('s.dateHeureDebut > :now')
            ->andWhere('p.id = :userId OR IDENTITY(s.organisateur) = :userId')
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function findLastSorties(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
}
