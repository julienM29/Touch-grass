<?php

namespace App\Tests\Entity;

use App\Entity\Lieu;
use App\Entity\Ville;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class LieuTest extends TestCase
{
    // Helper pour créer une ville valide réutilisable dans les tests
    private function creerVilleValide(): Ville
    {
        $ville = new Ville();
        $ville->setNom('Rennes');
        $ville->setCodePostal('35000');
        return $ville;
    }

    // Test : on peut créer un lieu et accéder à ses propriétés
    public function testCreerLieu(): void
    {
        $lieu = new Lieu();
        $lieu->setNom('ENI Rennes');
        $lieu->setRue('Rue Léo Lagrange');
        $lieu->setLatitude(48.038922);
        $lieu->setLongitude(-1.692391);
        $lieu->setVille($this->creerVilleValide());

        $this->assertSame('ENI Rennes', $lieu->getNom());
        $this->assertSame('Rue Léo Lagrange', $lieu->getRue());
        $this->assertSame(48.038922, $lieu->getLatitude());
        $this->assertSame(-1.692391, $lieu->getLongitude());
        $this->assertInstanceOf(Ville::class, $lieu->getVille());
    }

    // Test : l'id est null avant la persistance
    public function testIdNullAvantPersistance(): void
    {
        $lieu = new Lieu();
        $this->assertNull($lieu->getId());
    }

    // Test : les valeurs sont null par défaut
    public function testValeursNullParDefaut(): void
    {
        $lieu = new Lieu();
        $this->assertNull($lieu->getNom());
        $this->assertNull($lieu->getRue());
        $this->assertNull($lieu->getLatitude());
        $this->assertNull($lieu->getLongitude());
        $this->assertNull($lieu->getVille());
    }

    // Test : la collection de sorties est vide à la création
    public function testCollectionSortiesVideParDefaut(): void
    {
        $lieu = new Lieu();
        $this->assertCount(0, $lieu->getSorties());
    }

    // Test : lieu valide passe la validation
    public function testLieuValide(): void
    {
        $lieu = new Lieu();
        $lieu->setNom('ENI Rennes');
        $lieu->setRue('Rue Léo Lagrange');
        $lieu->setLatitude(48.038922);
        $lieu->setLongitude(-1.692391);
        $lieu->setVille($this->creerVilleValide());

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($lieu);
        $this->assertCount(0, $errors);
    }

    // Test : latitude hors limites
    public function testLatitudeHorsLimites(): void
    {
        $lieu = new Lieu();
        $lieu->setNom('ENI Rennes');
        $lieu->setRue('Rue Léo Lagrange');
        $lieu->setLatitude(200.0);
        $lieu->setLongitude(-1.692391);
        $lieu->setVille($this->creerVilleValide());

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($lieu);
        $this->assertGreaterThan(0, count($errors));
    }

    // Test : longitude hors limites
    public function testLongitudeHorsLimites(): void
    {
        $lieu = new Lieu();
        $lieu->setNom('ENI Rennes');
        $lieu->setRue('Rue Léo Lagrange');
        $lieu->setLatitude(48.038922);
        $lieu->setLongitude(200.0);
        $lieu->setVille($this->creerVilleValide());

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($lieu);
        $this->assertGreaterThan(0, count($errors));
    }

    // Test : setNom retourne bien l'objet (fluent interface)
    public function testSetNomRetourneStatic(): void
    {
        $lieu = new Lieu();
        $result = $lieu->setNom('ENI Rennes');
        $this->assertInstanceOf(Lieu::class, $result);
    }
}
