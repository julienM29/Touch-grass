<?php

namespace App\Tests\Utils;

use App\Utils\ImageLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageLoaderTest extends TestCase
{
    private string $testUploadsDir;
    private ImageLoader $imageLoader;

    // Exécuté avant chaque test : crée un dossier temporaire
    protected function setUp(): void
    {
        $this->testUploadsDir = sys_get_temp_dir() . '/test_uploads_' . uniqid();
        mkdir($this->testUploadsDir);
        $this->imageLoader = new ImageLoader($this->testUploadsDir);
    }

    // Exécuté après chaque test : nettoie le dossier temporaire
    protected function tearDown(): void
    {
        // Supprimer tous les fichiers du dossier temporaire
        foreach (glob($this->testUploadsDir . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->testUploadsDir);
    }

    // Helper : crée un faux fichier image uploadé
    private function creerFakeUploadedFile(string $filename = 'test.jpg'): UploadedFile
    {
        $tempFile = $this->testUploadsDir . '/' . $filename;
        file_put_contents($tempFile, 'fake image content');

        return new UploadedFile(
            $tempFile,
            $filename,
            'image/jpeg',
            null,
            true // test mode : désactive les vérifications d'upload PHP
        );
    }

    // Test : uploadImage retourne null si pas de fichier
    public function testUploadImageSansFichierRetourneNull(): void
    {
        $result = $this->imageLoader->uploadImage(null);
        $this->assertNull($result);
    }

    // Test : uploadImage retourne un nom de fichier
    public function testUploadImageRetourneNomFichier(): void
    {
        $uploadedFile = $this->creerFakeUploadedFile();
        $result = $this->imageLoader->uploadImage($uploadedFile);

        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    // Test : uploadImage crée bien le fichier dans le dossier
    public function testUploadImageCreeLeFichier(): void
    {
        $uploadedFile = $this->creerFakeUploadedFile();
        $filename = $this->imageLoader->uploadImage($uploadedFile);

        $this->assertFileExists($this->testUploadsDir . '/' . $filename);
    }

    // Test : replaceImage sans nouvelle image retourne l'ancienne
    public function testReplaceImageSansNouvelleImageGardeAncienne(): void
    {
        $result = $this->imageLoader->replaceImage(null, 'ancienne_image.jpg');
        $this->assertSame('ancienne_image.jpg', $result);
    }

    // Test : replaceImage sans nouvelle image et sans ancienne retourne null
    public function testReplaceImageSansRienRetourneNull(): void
    {
        $result = $this->imageLoader->replaceImage(null, null);
        $this->assertNull($result);
    }

    // Test : replaceImage supprime l'ancien fichier
    public function testReplaceImageSupprimeAncienFichier(): void
    {
        // Créer un ancien fichier
        $oldFilename = 'ancienne_image.jpg';
        $oldFilePath = $this->testUploadsDir . '/' . $oldFilename;
        file_put_contents($oldFilePath, 'old image content');

        $this->assertFileExists($oldFilePath);

        // Remplacer par une nouvelle image
        $newFile = $this->creerFakeUploadedFile('nouvelle_image.jpg');
        $this->imageLoader->replaceImage($newFile, $oldFilename);

        // L'ancien fichier doit avoir été supprimé
        $this->assertFileDoesNotExist($oldFilePath);
    }

    // Test : replaceImage retourne le nom du nouveau fichier
    public function testReplaceImageRetourneNouveauNom(): void
    {
        $oldFilename = 'ancienne_image.jpg';
        $oldFilePath = $this->testUploadsDir . '/' . $oldFilename;
        file_put_contents($oldFilePath, 'old image content');

        $newFile = $this->creerFakeUploadedFile('nouvelle_image.jpg');
        $result = $this->imageLoader->replaceImage($newFile, $oldFilename);

        $this->assertNotNull($result);
        $this->assertNotSame($oldFilename, $result);
    }

    // Test : replaceImage fonctionne sans ancien fichier
    public function testReplaceImageSansAncienFichier(): void
    {
        $newFile = $this->creerFakeUploadedFile();
        $result = $this->imageLoader->replaceImage($newFile, null);

        $this->assertNotNull($result);
        $this->assertFileExists($this->testUploadsDir . '/' . $result);
    }
}
