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

    public function findAllLieu() {
        $lieu1 = new Lieu();
        $lieu1->setNom('ENI Rennes')->setRue('Rue de la Conterie')->setLatitude(48.0390316332447)->setLongitude(-1.6924381725787767);
        $lieu2 = new Lieu();
        $lieu2->setNom('ENI Nantes')->setRue('Rue de la Conterie')->setLatitude(48.0390316332447)->setLongitude(-1.6924381725787767);

        return [$lieu1, $lieu2];
    }

    //    /**
    //     * @return Lieu[] Returns an array of Lieu objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Lieu
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
