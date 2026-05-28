<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Participant) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return Participant[] Returns an array of Participant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Participant
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findLastParticipants(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }
    public function findFilteredPaginated(array $filters, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.nom', 'ASC');

        // EMAIL
        if (!empty($filters['email'])) {
            $qb->andWhere('p.email LIKE :email')
                ->setParameter('email', '%' . $filters['email'] . '%');
        }

        // NOM
        if (!empty($filters['nom'])) {
            $qb->andWhere('p.nom LIKE :nom')
                ->setParameter('nom', '%' . $filters['nom'] . '%');
        }

        // PRENOM
        if (!empty($filters['prenom'])) {
            $qb->andWhere('p.prenom LIKE :prenom')
                ->setParameter('prenom', '%' . $filters['prenom'] . '%');
        }

        // TELEPHONE
        if (!empty($filters['telephone'])) {
            $qb->andWhere('p.telephone LIKE :telephone')
                ->setParameter('telephone', '%' . $filters['telephone'] . '%');
        }

        // ACTIF
        if ($filters['actif'] !== null && $filters['actif'] !== '') {

            if ($filters['actif'] == '1') {
                $qb->andWhere('p.actif = :actif')
                    ->setParameter('actif', true);
            }

            if ($filters['actif'] == '0') {
                $qb->andWhere('p.actif = :actif')
                    ->setParameter('actif', false);
            }
        }

        // PAGINATION
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit + 1);

        return $qb->getQuery()->getResult();
    }

    public function anonymizeUser(Participant $user, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): void
    {
        $id = $user->getId();

        $user->setEmail('deleted_' . $id . '@deleted.local');
        $user->setPrenom('Anonymous');
        $user->setNom('Anonymous');
        $user->setPseudo('deleted_user_' . $id);
        $user->setActif(false);
        $now = new \DateTime();
        $sorties = $sortieRepository->findFuturSortiesByOrganisateur($user->getId());
        foreach ($sorties as $sortie) {
            $sortie->setMotifAnnulation(
                'Événement annulé suite à la suppression du compte organisateur'
            );
            $sortie->setDateModification($now);
        }
        // Enlever le user des sorties pas encore commencé
        $sortiesInscrit = $user->getSorties();
        foreach ($sortiesInscrit as $sortie) {
            $sortie->removeParticipant($user);
            $user->removeSorty($sortie);
        }
        $entityManager->flush();
    }
}
