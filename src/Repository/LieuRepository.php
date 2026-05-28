<?php

namespace App\Repository;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lieu>
 */
class LieuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lieu::class);
    }

    public function findAllLieu(): array
    {
        return $this->findAll();
    }

    public function findLieuById(int $id) {
        return $this->find($id);
    }
    public function findFilteredPaginated(array $filters, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.ville', 'v')
            ->addSelect('v')
            ->orderBy('l.nom', 'ASC');

        // LIEU
        if (!empty($filters['lieu'])) {
            $qb->andWhere('l.nom LIKE :lieu')
                ->setParameter('lieu', '%' . $filters['lieu'] . '%');
        }

        // VILLE
        if (!empty($filters['ville'])) {
            $qb->andWhere('v.nom LIKE :ville')
                ->setParameter('ville', '%' . $filters['ville'] . '%');
        }

        // CODE POSTAL
        if (!empty($filters['codePostal'])) {
            $qb->andWhere('v.codePostal LIKE :cp')
                ->setParameter('cp', '%' . $filters['codePostal'] . '%');
        }

        // PAGINATION (+1 trick)
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit + 1);

        return $qb->getQuery()->getResult();
    }}
