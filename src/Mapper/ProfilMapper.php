<?php

namespace App\Mapper;

 use App\Dto\ProfilDTO;
 use App\Entity\Participant;

class ProfilMapper
{
    public function fromEntity(Participant $user): ProfilDTO
    {
        $dto = new ProfilDTO();

        $dto->email = $user->getEmail();
        $dto->pseudo = $user->getPseudo();
        $dto->prenom = $user->getPrenom();
        $dto->nom = $user->getNom();
        $dto->telephone = $user->getTelephone();
        $dto->site = $user->getSite();
        $dto->image = $user->getImage();

        return $dto;
    }

    public function toEntity(ProfilDTO $dto, Participant $user): Participant
    {
        $user->setEmail($dto->email);
        $user->setPseudo($dto->pseudo);
        $user->setPrenom($dto->prenom);
        $user->setNom($dto->nom);
        $user->setTelephone($dto->telephone);
        $user->setSite($dto->site);
        $user->setImage($dto->image);

        return $user;
    }
}
