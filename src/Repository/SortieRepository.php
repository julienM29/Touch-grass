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
    public function findFilteredPaginated(array $filters, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.lieu', 'l')
            ->addSelect('l')
            ->leftJoin('s.organisateur', 'o')
            ->addSelect('o')
            ->orderBy('s.dateHeureDebut', 'DESC');
        // NOM SORTIE
        if (!empty($filters['nom'])) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filters['nom'] . '%');
        }
        // VILLE
        if (!empty($filters['ville'])) {
            $qb->andWhere('l.ville LIKE :ville')
                ->setParameter('ville', '%' . $filters['ville'] . '%');
        }
        // LIEU
        if (!empty($filters['lieu'])) {
            $qb->andWhere('l.nom LIKE :lieu')
                ->setParameter('lieu', '%' . $filters['lieu'] . '%');
        }
        // DATE

        if (!empty($filters['date'])) {
            $date = new \DateTimeImmutable($filters['date']);
            $start = $date->setTime(0, 0);
            $end = $date->setTime(23, 59, 59);

            $qb->andWhere('s.dateHeureDebut BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }
        // STATUS
        if (!empty($filters['status'])) {

            $now = new \DateTime();

            switch ($filters['status']) {

                case 'upcoming':
                    $qb->andWhere('s.dateHeureDebut > :now')
                        ->setParameter('now', $now);
                    break;

                case 'past':
                    $qb->andWhere('s.dateHeureDebut < :now')
                        ->setParameter('now', $now);
                    break;

                case 'cancelled':
                    $qb->andWhere('s.motifAnnulation IS NOT NULL');
                    break;

                case 'ongoing':
                    $qb->andWhere('s.dateHeureDebut <= :now')
                        ->andWhere('DATE_ADD(s.dateHeureDebut, s.duree) >= :now')
                        ->setParameter('now', $now);
                    break;
            }
        }
        // PAGINATION
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit + 1)
            ->orderBy('s.dateHeureDebut', 'ASC');
        return $qb->getQuery()->getResult();
    }
}
