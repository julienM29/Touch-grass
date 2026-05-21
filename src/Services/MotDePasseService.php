<?php

namespace App\Services;

use App\Entity\Participant;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
class MotDePasseService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function changePassword(
        Participant $user,
        string $currentPassword,
        string $newPassword,
        FormInterface $form
    ): bool {
        // 1. vérifier ancien mot de passe
        if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
            $form->get('current_password')
                ->addError(new FormError('Mot de passe actuel incorrect'));
            return false;
        }
        if ($this->passwordHasher->isPasswordValid($user, $newPassword)) {
            $form->get('new_password')->get('first')
                ->addError(new FormError('Le nouveau mot de passe doit être différent de l’ancien.'));
            return false;
        }

        // 2. hash nouveau mot de passe
        $hashed = $this->passwordHasher->hashPassword($user, $newPassword);

        $user->setPassword($hashed);

        return true;
    }
}
