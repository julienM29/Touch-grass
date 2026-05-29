<?php

namespace App\Tests\Functional;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserScenarioTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->nettoyerBdd();
        $this->creerDonneesDeBase();
    }

    protected function tearDown(): void
    {
        $this->nettoyerBdd();
        parent::tearDown();
    }

    // -----------------------------------------------------------
    // Nettoie la BDD de test avant/après chaque test
    // -----------------------------------------------------------
    private function nettoyerBdd(): void
    {
        $this->em->createQuery('DELETE FROM App\Entity\Participant p WHERE p.email = :email')
            ->setParameter('email', 'test_scenario@test.fr')
            ->execute();
    }

    // -----------------------------------------------------------
    // Crée les données minimales nécessaires (un site)
    // -----------------------------------------------------------
    private function creerDonneesDeBase(): void
    {
        $siteExistant = $this->em->getRepository(Site::class)->findOneBy(['nom' => 'Campus Test']);
        if (!$siteExistant) {
            $site = new Site();
            $site->setNom('Campus Test');
            $this->em->persist($site);
            $this->em->flush();
        }
    }

    // -----------------------------------------------------------
    // SCÉNARIO COMPLET
    // -----------------------------------------------------------
    public function testScenarioComplet(): void
    {
        // -------------------------------------------------------
        // ÉTAPE 1 : Création de compte
        // -------------------------------------------------------
        $crawler = $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        $site = $this->em->getRepository(Site::class)->findOneBy(['nom' => 'Campus Test']);

        $this->client->submitForm('Créer le compte', [
            'registration_form[email]' => 'test_scenario@test.fr',
            'registration_form[pseudo]' => 'test_scenario',
            'registration_form[prenom]' => 'Test',
            'registration_form[nom]' => 'Scenario',
            'registration_form[telephone]' => '0612345678',
            'registration_form[site]' => $site->getId(),
            'registration_form[plainPassword][first]' => 'Password1!',
            'registration_form[plainPassword][second]' => 'Password1!',
            'registration_form[agreeTerms]' => true,
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        dump('✅ Étape 1 : Compte créé avec succès');

        // -------------------------------------------------------
        // ÉTAPE 2 : Connexion
        // -------------------------------------------------------
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Connexion', [
            '_username' => 'test_scenario@test.fr',
            '_password' => 'Password1!',
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        // Vérifier qu'on est bien connecté WIP
        //        $this->assertSelectorNotExists('a[href="/login"]');

        dump('✅ Étape 2 : Connexion réussie');

        // -------------------------------------------------------
        // ÉTAPE 3 : Modification du profil
        // -------------------------------------------------------
        $this->client->request('GET', '/profil/modify');
        $this->assertResponseIsSuccessful();

        $site = $this->em->getRepository(Site::class)->findOneBy(['nom' => 'Campus Test']);

        $this->client->submitForm('Enregistrer', [
            'profil_form[pseudo]'     => 'test_modifie',  // max 20 caractères
            'profil_form[prenom]'     => 'TestModifié',
            'profil_form[nom]'        => 'ScenarioModifié',
            'profil_form[telephone]'  => '0698765432',
            'profil_form[site]'       => $site->getId(),
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        dump('✅ Étape 3 : Profil modifié avec succès');

        // -------------------------------------------------------
        // ÉTAPE 4 : Parcourir les sorties
        // -------------------------------------------------------
        $this->client->request('GET', '/sortie/');
        $this->assertResponseIsSuccessful();

        dump('✅ Étape 4 : Liste des sorties accessible');

        // -------------------------------------------------------
        // ÉTAPE 5 : Inscription à une sortie
        // -------------------------------------------------------
//        $sortie = $this->em->getRepository(\App\Entity\Sortie::class)->findOneBy([]);
//
//        if ($sortie) {
//            // Visiter la page de la sortie pour initialiser la session et récupérer le token
//            $crawler = $this->client->request('GET', '/sortie/' . $sortie->getId());
//            // La route d'inscription retourne du JSON, pas une redirection
//            $this->assertResponseIsSuccessful();
//
//            $responseData = json_decode($this->client->getResponse()->getContent(), true);
//            $this->assertTrue($responseData['success']);
//            $this->assertTrue($responseData['registered']);
//
//            dump('✅ Étape 5 : Inscription à une sortie réussie - ' . $responseData['message']);
//        } else {
//            dump('⚠️ Étape 5 : Aucune sortie disponible en BDD de test');
//        }
    }
}
