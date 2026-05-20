<?php

namespace App\Services;

use App\Entity\Participant;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private string $uploadsDirectory // Symfony fait le lien vers le parameters dans le services.yaml qui définit la route vers le dossier d'upload

    ) {}

    /**
     * Hash et définit le mot de passe si fourni
     */
    public function handlePassword(Participant $participant, ?string $plainPassword): void
    {
        if (!$plainPassword) {
            return;
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            $participant,
            $plainPassword
        );

        $participant->setPassword($hashedPassword);
    }

    /**
     * Upload une image et retourne le nom du fichier
     */
    public function uploadImage(?UploadedFile $imageFile): ?string
    {
        if (!$imageFile) {
            return null;
        }

        $newFilename = uniqid().'.'.$imageFile->guessExtension();

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
