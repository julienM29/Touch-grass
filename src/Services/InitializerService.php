<?php

namespace App\Services;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;

class InitializerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function initializeSorties(): void
    {
        $sortiesData = [
            [
                'id' => 1,
                'nom' => 'Randonnee en foret',
                'dateHeureDebut' => '2026-05-10 09:00:00',
                'duree' => 'PT3H',
                'dateLimiteInscription' => '2026-05-08 23:59:00',
                'nbInscriptionsMax' => 20,
                'description' => 'Balade en foret de Broceliande, prevoir chaussures de marche.',
                'dateOuvertureInscription' => '2026-04-20 00:00:00',
                'image' => 'sortie-01.webp',
                'siteId' => 1,
                'lieuId' => 1,
                'organisateurId' => 1,
            ],
            [
                'id' => 2,
                'nom' => 'Soiree jeux de societe',
                'dateHeureDebut' => '2026-05-21 19:00:00',
                'duree' => 'PT4H',
                'dateLimiteInscription' => '2026-05-20 23:59:00',
                'nbInscriptionsMax' => 12,
                'description' => 'Venez decouvrir nos dernieres acquisitions ludiques.',
                'dateOuvertureInscription' => '2026-05-01 00:00:00',
                'image' => 'sortie-02.webp',
                'siteId' => 1,
                'lieuId' => 2,
                'organisateurId' => 2,
            ],
            [
                'id' => 3,
                'nom' => 'Atelier peinture',
                'dateHeureDebut' => '2026-05-25 14:00:00',
                'duree' => 'PT2H',
                'dateLimiteInscription' => '2026-05-24 23:59:00',
                'nbInscriptionsMax' => 10,
                'description' => 'Initiation aquarelle, materiel fourni.',
                'dateOuvertureInscription' => '2026-05-05 00:00:00',
                'image' => 'sortie-03.jpg',
                'siteId' => 2,
                'lieuId' => 3,
                'organisateurId' => 3,
            ],
            [
                'id' => 4,
                'nom' => 'Tournoi de ping-pong',
                'dateHeureDebut' => '2026-05-30 10:00:00',
                'duree' => 'PT5H',
                'dateLimiteInscription' => '2026-05-28 23:59:00',
                'nbInscriptionsMax' => 16,
                'description' => 'Tournoi en double, inscriptions par equipe de 2.',
                'dateOuvertureInscription' => '2026-05-15 00:00:00',
                'image' => 'sortie-04.webp',
                'siteId' => 2,
                'lieuId' => 4,
                'organisateurId' => 1,
            ],
            [
                'id' => 5,
                'nom' => 'Visite musee des Beaux-Arts',
                'dateHeureDebut' => '2026-06-05 11:00:00',
                'duree' => 'PT2H30M',
                'dateLimiteInscription' => '2026-06-01 23:59:00',
                'nbInscriptionsMax' => 25,
                'description' => 'Visite guidee de la collection permanente.',
                'dateOuvertureInscription' => '2026-05-25 00:00:00',
                'image' => 'sortie-05.jpg',
                'siteId' => 1,
                'lieuId' => 5,
                'organisateurId' => 2,
            ],
            [
                'id' => 6,
                'nom' => 'Karaoke party',
                'dateHeureDebut' => '2026-06-10 20:00:00',
                'duree' => 'PT3H',
                'dateLimiteInscription' => '2026-06-08 23:59:00',
                'nbInscriptionsMax' => 30,
                'description' => 'Soiree karaoke dans une salle privatisee.',
                'dateOuvertureInscription' => '2026-06-01 00:00:00',
                'image' => 'sortie-06.jpg',
                'siteId' => 3,
                'lieuId' => 2,
                'organisateurId' => 4,
            ],
            [
                'id' => 7,
                'nom' => 'Sortie escalade',
                'dateHeureDebut' => '2026-05-29 09:30:00',
                'duree' => 'PT4H',
                'dateLimiteInscription' => '2026-05-20 23:59:00',
                'nbInscriptionsMax' => 8,
                'description' => 'Escalade en salle pour tous niveaux, encadrement prevu.',
                'dateOuvertureInscription' => '2026-05-01 00:00:00',
                'image' => 'sortie-07.webp',
                'siteId' => 2,
                'lieuId' => 6,
                'organisateurId' => 3,
            ],
            [
                'id' => 8,
                'nom' => 'Pique-nique au parc',
                'dateHeureDebut' => '2026-05-24 12:00:00',
                'duree' => 'PT3H',
                'dateLimiteInscription' => '2026-05-23 23:59:00',
                'nbInscriptionsMax' => 50,
                'description' => 'Grand pique-nique collectif, chacun apporte quelque chose.',
                'dateOuvertureInscription' => '2026-05-10 00:00:00',
                'image' => 'sortie-08.jpg',
                'siteId' => 1,
                'lieuId' => 7,
                'organisateurId' => 1,
            ],
            [
                'id' => 9,
                'nom' => 'Cine en plein air',
                'dateHeureDebut' => '2026-07-25 21:00:00',
                'duree' => 'PT2H',
                'dateLimiteInscription' => '2026-07-22 23:59:00',
                'nbInscriptionsMax' => 40,
                'description' => 'Projection de Spirited Away sous les etoiles.',
                'dateOuvertureInscription' => '2026-06-15 00:00:00',
                'image' => 'sortie-09.jpg',
                'siteId' => 3,
                'lieuId' => 8,
                'organisateurId' => 2,
            ],
            [
                'id' => 10,
                'nom' => 'Cours de cuisine japonaise',
                'dateHeureDebut' => '2026-08-02 15:00:00',
                'duree' => 'PT3H',
                'dateLimiteInscription' => '2026-07-28 23:59:00',
                'nbInscriptionsMax' => 10,
                'description' => 'Preparation de sushis et ramens, tablier fourni.',
                'dateOuvertureInscription' => '2026-07-01 00:00:00',
                'image' => 'sortie-10.jpg',
                'siteId' => 2,
                'lieuId' => 3,
                'organisateurId' => 4,
            ],
        ];

        foreach ($sortiesData as $data) {
            $sortie = $this->em->getRepository(Sortie::class)->find($data['id']);

            if (!$sortie) {
                $sortie = new Sortie();
            }

            $site = $this->em->getRepository(Site::class)->find($data['siteId']);
            $lieu = $this->em->getRepository(Lieu::class)->find($data['lieuId']);
            $organisateur = $this->em->getRepository(Participant::class)->find($data['organisateurId']);

            if (!$site || !$lieu || !$organisateur) {
                continue;
            }

            $sortie
                ->setNom($data['nom'])
                ->setDateHeureDebut(new \DateTime($data['dateHeureDebut']))
                ->setDuree(new \DateInterval($data['duree']))
                ->setDateLimiteInscription(new \DateTime($data['dateLimiteInscription']))
                ->setNbInscriptionsMax($data['nbInscriptionsMax'])
                ->setDescription($data['description'])
                ->setDateOuvertureInscription(new \DateTime($data['dateOuvertureInscription']))
                ->setImage($data['image'])
                ->setDateModification(new \DateTime())
                ->setSiteOrganisateur($site)
                ->setLieu($lieu)
                ->setOrganisateur($organisateur);

            $this->em->persist($sortie);
        }

        $this->em->flush();
    }
}
