<?php

namespace App\Tests\Entity;

use App\Entity\Ville;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class VilleTest extends TestCase
{
    // Test de base : on peut créer une ville et accéder à ses propriétés
    public function testCreerVille(): void
    {
        $ville = new Ville();
        $ville->setNom('Rennes');
        $ville->setCodePostal('35000');

        $this->assertSame('Rennes', $ville->getNom());
        $this->assertSame('35000', $ville->getCodePostal());
    }

    // Test : l'id est null avant la persistance en BDD
    public function testIdNullAvantPersistance(): void
    {
        $ville = new Ville();
        $this->assertNull($ville->getId());
    }

    // Test : les valeurs sont null par défaut
    public function testValeursNullParDefaut(): void
    {
        $ville = new Ville();
        $this->assertNull($ville->getNom());
        $this->assertNull($ville->getCodePostal());
    }

    // Test : setNom retourne bien l'objet (fluent interface)
    public function testSetNomRetourneStatic(): void
    {
        $ville = new Ville();
        $result = $ville->setNom('Nantes');
        $this->assertInstanceOf(Ville::class, $result);
    }

    // Test : setCodePostal retourne bien l'objet (fluent interface)
    public function testSetCodePostalRetourneStatic(): void
    {
        $ville = new Ville();
        $result = $ville->setCodePostal('44000');
        $this->assertInstanceOf(Ville::class, $result);
    }

    // Test : la collection de lieux est vide à la création
    public function testCollectionLieusVideeParDefaut(): void
    {
        $ville = new Ville();
        $this->assertCount(0, $ville->getLieus());
    }

    // --- TESTS DE VALIDATION --- //
    // Test : code postal valide
    public function testCodePostalValide(): void
    {
        $ville = new Ville();
        $ville->setNom('Rennes');
        $ville->setCodePostal('35000');

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($ville);
        $this->assertCount(0, $errors);
    }

    // Test : code postal trop court
    public function testCodePostalTropCourt(): void
    {
        $ville = new Ville();
        $ville->setNom('Rennes');
        $ville->setCodePostal('350');

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($ville);
        $this->assertGreaterThan(0, count($errors));
    }

    // Test : code postal avec des lettres
    public function testCodePostalAvecLettres(): void
    {
        $ville = new Ville();
        $ville->setNom('Rennes');
        $ville->setCodePostal('3500A');

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($ville);
        $this->assertGreaterThan(0, count($errors));
    }

    // Test : nom vide
    public function testNomVide(): void
    {
        $ville = new Ville();
        $ville->setNom('');
        $ville->setCodePostal('35000');

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($ville);
        $this->assertGreaterThan(0, count($errors));
    }
}
