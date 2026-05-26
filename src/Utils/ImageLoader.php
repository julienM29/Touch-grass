<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageLoader
{
    public function __construct(
        private string $uploadsDirectory
    ){}

    /**
     * Upload une image et retourne le nom du fichier
     */
    public function uploadImage(?UploadedFile $imageFile): ?string
    {
        if (!$imageFile) {
            return null;
        }

        $newFilename = uniqid() . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move(
                $this->uploadsDirectory,
                $newFilename
            );
        } catch (FileException $e) {
            return null;
        }

        return $newFilename;
    }

    /**
     * Remplace une image existante par une nouvelle
     * Supprime l'ancien fichier s'il existe
     */
    public function replaceImage(?UploadedFile $newImageFile, ?string $oldFilename): ?string
    {
        if (!$newImageFile) {
            return $oldFilename; // pas de nouvelle image -> on garde l'ancienne
        }

        // Supprimer l'ancien fichier s'il existe
        if ($oldFilename) {
            $oldFilePath = $this->uploadsDirectory . '/' . $oldFilename;
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        // Uploader la nouvelle image
        return $this->uploadImage($newImageFile);
    }

}
