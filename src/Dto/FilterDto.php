<?php

namespace App\Dto;
use DateTime;

class FilterDto
{


    public ?string $site = null;
    public ?string $word = null;
    public ?DateTime $dateMin = null;
    public ?DateTime $dateMax = null;
    public ?bool $organisateur = null;
    public ?bool $registered = null;
    public ?bool $notRegistered = null;
    public ?bool $finished = null;

    /**
     * @param string|null $site
     * @param string|null $word
     * @param DateTime|null $dateMin
     * @param DateTime|null $dateMax
     * @param bool|null $organisateur
     * @param bool|null $registered
     * @param bool|null $notRegistered
     * @param bool|null $finished
     */
    public function __construct(?string $site, ?string $word, ?DateTime $dateMin, ?DateTime $dateMax, ?bool $organisateur, ?bool $registered, ?bool $notRegistered, ?bool $finished)
    {
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
