<?php

namespace App\Repository;

use App\Dto\FilterDto;
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

    public function findAllActiveSorties(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.dateHeureDebut > :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('s.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();
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
            ->andWhere('s.motifAnnulation IS NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPastSorties(): int
    {
        return $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.dateHeureDebut < :now')
            ->andWhere('s.motifAnnulation IS NULL')
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
        return (int)$this->createQueryBuilder('s')
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
        return (int)$this->createQueryBuilder('s')
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

                // SORTIES À VENIR
                case 'upcoming':

                    $qb->andWhere('s.motifAnnulation IS NULL')
                        ->andWhere('s.dateHeureDebut > :now')
                        ->setParameter('now', $now);

                    break;

                // SORTIES PASSÉES
                case 'past':

                    $qb->andWhere('s.motifAnnulation IS NULL')
                        ->andWhere('s.dateHeureDebut < :now')
                        ->setParameter('now', $now);

                    break;

                // SORTIES ANNULÉES
                case 'cancelled':

                    $qb->andWhere('s.motifAnnulation IS NOT NULL');

                    break;

                // SORTIES EN COURS
                case 'ongoing':

                    $qb->andWhere('s.motifAnnulation IS NULL');

                    $sorties = $qb->getQuery()->getResult();

                    return array_filter($sorties, function ($sortie) use ($now) {

                        $dateDebut = $sortie->getDateHeureDebut();
                        $dateFin = (clone $dateDebut)->add($sortie->getDuree());

                        return $dateDebut <= $now && $dateFin >= $now;
                    });
            }

        } else {

            // PAR DÉFAUT : exclure les annulées
            $qb->andWhere('s.motifAnnulation IS NULL');
        }

        // PAGINATION
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit + 1)
            ->orderBy('s.dateHeureDebut', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findRecentSortiesByParticipant(int $userId): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.participants', 'p')
            ->andWhere('s.dateHeureDebut < :now')
            ->andWhere('p.id = :userId OR IDENTITY(s.organisateur) = :userId')
            ->setParameter('userId', $userId)
            ->setParameter('now', new \DateTime())
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    public function findFilteredSorties(FilterDto $filters, int $userId): array
    {
        /* Jointure des tables sortie, lieu et ville */
        $queryBuilder = $this->createQueryBuilder('s')
            ->leftJoin('s.lieu', 'l')
            ->leftJoin('l.ville', 'v')
            ->leftJoin('s.participants', 'p');

        /* Recherche par mot-clef */
        if ($filters->word !== null && trim($filters->word) !== '') {
            $queryBuilder
                ->andWhere('(s.nom LIKE :word OR v.nom LIKE :word)')
                ->setParameter('word', '%' . trim($filters->word) . '%');
        }

        /* Recherche par date */
        if ($filters->dateMin !== null && $filters->dateMax !== null) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut BETWEEN :dateMin AND :dateMax')
                ->setParameter('dateMin', $filters->dateMin)
                ->setParameter('dateMax', $filters->dateMax);
        } elseif ($filters->dateMin !== null) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut >= :dateMin')
                ->setParameter('dateMin', $filters->dateMin);
        } elseif ($filters->dateMax !== null) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut <= :dateMax')
                ->setParameter('dateMax', $filters->dateMax);
        }

        /* Recherche par site organisateur */
        if($filters->site !== null) {
            $queryBuilder
                ->andWhere('s.siteOrganisateur = :site')
                ->setParameter('site', $filters->site);
        }

        /* Recherche par organisateur est l'utilisateur courant */
        if($filters->organisateur) {
            $queryBuilder
                ->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $userId);
        }

        /* Recherche par participants contient l'utilisateur courant */
        if ($filters->registered) {
            $queryBuilder
                ->andWhere('p.id = :participant')
                ->setParameter('participant', $userId);
        }

        /* Recherche par participants ne contient pas l'utilisateur courant */
        if ($filters->notRegistered) {
            $queryBuilder
                ->andWhere('p.id != :participant')
                ->setParameter('participant', $userId);
        }

        /* Recherche par sortie terminée */
        if ($filters->finished) {
            $queryBuilder
                ->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        return $queryBuilder
            ->orderBy('s.dateHeureDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
