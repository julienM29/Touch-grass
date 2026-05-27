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
}
