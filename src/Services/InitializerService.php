<?php

namespace App\Services;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InitializerService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function initializeSites(): array
    {
        $sitesData = [
            'Campus de Rennes',
            'Campus de Nantes',
            'Campus de Quimper',
            'Campus de Niort',
        ];

        $sites = [];

        foreach ($sitesData as $siteName) {
            $site = $this->em->getRepository(Site::class)->findOneBy([
                'nom' => $siteName,
            ]);

            if (!$site) {
                $site = new Site();
                $site->setNom($siteName);

                $this->em->persist($site);
            }

            $sites[$siteName] = $site;
        }

        $this->em->flush();

        return $sites;
    }

    public function initializeParticipants(): array
    {
        $sites = $this->initializeSites();

        $participantsData = [
            [
                'email' => 'admin@touchgrass.test',
                'pseudo' => 'admin',
                'nom' => 'Admin',
                'prenom' => 'TouchGrass',
                'telephone' => '0101010101',
                'roles' => ['ROLE_ADMIN'],
                'site' => 'Campus de Rennes',
                'password' => 'password',
            ],
            [
                'email' => 'alice@touchgrass.test',
                'pseudo' => 'alice',
                'nom' => 'Martin',
                'prenom' => 'Alice',
                'telephone' => '0202020202',
                'roles' => [],
                'site' => 'Campus de Rennes',
                'password' => 'password',
            ],
            [
                'email' => 'bob@touchgrass.test',
                'pseudo' => 'bob',
                'nom' => 'Durand',
                'prenom' => 'Bob',
                'telephone' => '0303030303',
                'roles' => [],
                'site' => 'Campus de Nantes',
                'password' => 'password',
            ],
            [
                'email' => 'claire@touchgrass.test',
                'pseudo' => 'claire',
                'nom' => 'Le Gall',
                'prenom' => 'Claire',
                'telephone' => '0404040404',
                'roles' => [],
                'site' => 'Campus de Quimper',
                'password' => 'password',
            ],
        ];

        $participants = [];



        foreach ($participantsData as $data) {
            $participant = $this->em->getRepository(Participant::class)->findOneBy([
                'email' => $data['email'],
            ]);

            if (!$participant) {
                $participant = new Participant();
                $participant
                    ->setEmail($data['email'])
                    ->setPseudo($data['pseudo'])
                    ->setNom($data['nom'])
                    ->setPrenom($data['prenom'])
                    ->setTelephone($data['telephone'])
                    ->setRoles($data['roles'])
                    ->setSite($sites[$data['site']])
                    ->setAdministrateur(in_array('ROLE_ADMIN', $data['roles'], true))
                    ->setActif(true);

                $participant->setPassword(
                    $this->passwordHasher->hashPassword($participant, $data['password'])
                );

                $this->em->persist($participant);
            }

            $participants[$data['email']] = $participant;
        }

        $this->em->flush();

        return $participants;
    }

    public function initializeVilles(): array
    {
        $villesData = [
            ['nom' => 'Rennes', 'codePostal' => '35000'],
            ['nom' => 'Nantes', 'codePostal' => '44000'],
            ['nom' => 'Quimper', 'codePostal' => '29000'],
            ['nom' => 'Niort', 'codePostal' => '79000'],
            ['nom' => 'Brest', 'codePostal' => '29200'],
        ];

        $villes = [];

        foreach ($villesData as $data) {
            $ville = $this->em->getRepository(Ville::class)->findOneBy([
                'nom' => $data['nom'],
                'codePostal' => $data['codePostal'],
            ]);

            if (!$ville) {
                $ville = new Ville();
                $ville
                    ->setNom($data['nom'])
                    ->setCodePostal($data['codePostal']);

                $this->em->persist($ville);
            }

            $villes[$data['nom']] = $ville;
        }

        $this->em->flush();

        return $villes;
    }

    public function initializeLieux(): array
    {
        $villes = $this->initializeVilles();

        $lieuxData = [
            [
                'nom' => 'Forêt de Brocéliande',
                'rue' => 'Route de la Forêt',
                'ville' => 'Rennes',
                'latitude' => 48.1120,
                'longitude' => -1.6819,
            ],
            [
                'nom' => 'Maison des Jeux',
                'rue' => '12 rue des Dés',
                'ville' => 'Rennes',
                'latitude' => 48.1173,
                'longitude' => -1.6778,
            ],
            [
                'nom' => 'Atelier des Couleurs',
                'rue' => '8 rue des Artistes',
                'ville' => 'Nantes',
                'latitude' => 47.2184,
                'longitude' => -1.5536,
            ],
            [
                'nom' => 'Gymnase municipal',
                'rue' => '3 avenue du Sport',
                'ville' => 'Nantes',
                'latitude' => 47.2150,
                'longitude' => -1.5586,
            ],
            [
                'nom' => 'Musée des Beaux-Arts',
                'rue' => '20 quai Émile Zola',
                'ville' => 'Rennes',
                'latitude' => 48.1096,
                'longitude' => -1.6743,
            ],
            [
                'nom' => 'Salle Escalade Ouest',
                'rue' => '14 rue des Grimpeurs',
                'ville' => 'Quimper',
                'latitude' => 47.9960,
                'longitude' => -4.1020,
            ],
            [
                'nom' => 'Parc de la Tête Verte',
                'rue' => '1 allée du Parc',
                'ville' => 'Niort',
                'latitude' => 46.3237,
                'longitude' => -0.4648,
            ],
            [
                'nom' => 'Esplanade Cinéma',
                'rue' => '5 place des Étoiles',
                'ville' => 'Brest',
                'latitude' => 48.3904,
                'longitude' => -4.4861,
            ],
        ];

        $lieux = [];

        foreach ($lieuxData as $data) {
            $lieu = $this->em->getRepository(Lieu::class)->findOneBy([
                'nom' => $data['nom'],
            ]);

            if (!$lieu) {
                $lieu = new Lieu();
                $lieu
                    ->setNom($data['nom'])
                    ->setRue($data['rue'])
                    ->setLatitude($data['latitude'])
                    ->setLongitude($data['longitude'])
                    ->setVille($villes[$data['ville']]);

                $this->em->persist($lieu);
            }

            $lieux[$data['nom']] = $lieu;
        }

        $this->em->flush();

        return $lieux;
    }

    public function initializeSorties(): void
    {
        $sites = $this->initializeSites();

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
                'image' => 'event_registration-01.webp',
                'siteName' => 'Campus de Rennes',
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
                'image' => 'event_registration-02.webp',
                'siteName' => 'Campus de Rennes',
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
                'image' => 'event_registration-03.jpg',
                'siteName' => 'Campus de Nantes',
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
                'image' => 'event_registration-04.webp',
                'siteName' => 'Campus de Nantes',
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
                'image' => 'event_registration-05.jpg',
                'siteName' => 'Campus de Rennes',
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
                'image' => 'event_registration-06.jpg',
                'siteName' => 'Campus de Quimper',
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
                'image' => 'event_registration-07.webp',
                'siteName' => 'Campus de Nantes',
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
                'image' => 'event_registration-08.jpg',
                'siteName' => 'Campus de Rennes',
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
                'image' => 'event_registration-09.jpg',
                'siteName' => 'Campus de Quimper',
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
                'image' => 'event_registration-10.jpg',
                'siteName' => 'Campus de Niort',
                'lieuId' => 3,
                'organisateurId' => 4,
            ],
        ];

        foreach ($sortiesData as $data) {
            $sortie = $this->em->getRepository(Sortie::class)->find($data['id']);

            if (!$sortie) {
                $sortie = new Sortie();
            }

            $site = $sites[$data['siteName']] ?? null;
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

    public function resetAllData(): void
    {
        $connection = $this->em->getConnection();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        $connection->executeStatement('DELETE FROM sortie_participant');
        $connection->executeStatement('DELETE FROM event_registration');
        $connection->executeStatement('DELETE FROM participant');
        $connection->executeStatement('DELETE FROM lieu');
        $connection->executeStatement('DELETE FROM ville');
        $connection->executeStatement('DELETE FROM site');

        $connection->executeStatement('ALTER TABLE event_registration AUTO_INCREMENT = 1');
        $connection->executeStatement('ALTER TABLE participant AUTO_INCREMENT = 1');
        $connection->executeStatement('ALTER TABLE lieu AUTO_INCREMENT = 1');
        $connection->executeStatement('ALTER TABLE ville AUTO_INCREMENT = 1');
        $connection->executeStatement('ALTER TABLE site AUTO_INCREMENT = 1');

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');

        $this->em->clear();

        $this->initializeSites();
        $this->initializeVilles();
        $this->initializeLieux();
        $this->initializeParticipants();
        $this->initializeSorties();
    }
}
