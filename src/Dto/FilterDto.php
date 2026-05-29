<?php

namespace App\Dto;

use App\Entity\Site;
use DateTime;

class FilterDto
{
    public ?Site $site = null;
    public ?string $word = null;
    public ?DateTime $dateMin = null;
    public ?DateTime $dateMax = null;
    public ?bool $organisateur = null;
    public ?bool $registered = null;
    public ?bool $notRegistered = null;
    public ?bool $finished = null;

    public function __construct(
        ?Site $site = null,
        ?string $word = null,
        ?DateTime $dateMin = null,
        ?DateTime $dateMax = null,
        ?bool $organisateur = null,
        ?bool $registered = null,
        ?bool $notRegistered = null,
        ?bool $finished = null
    ) {
        $this->site = $site;
        $this->word = $word;
        $this->dateMin = $dateMin;
        $this->dateMax = $dateMax;
        $this->organisateur = $organisateur;
        $this->registered = $registered;
        $this->notRegistered = $notRegistered;
        $this->finished = $finished;
    }
}
