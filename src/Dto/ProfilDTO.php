<?php

namespace App\Dto;

use App\Entity\Site;
use Symfony\Component\Validator\Constraints as Assert;

class ProfilDTO
{
    public ?string $email = null;

    public ?string $pseudo = null;

    #[Assert\NotBlank]
    public ?string $prenom = null;

    #[Assert\NotBlank]
    public ?string $nom = null;

    #[Assert\Regex('/^[0-9]{10}$/')]
    public ?string $telephone = null;

    public ?Site $site = null;

    public ?string $image = null;
}
