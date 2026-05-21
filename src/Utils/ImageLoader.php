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

}
