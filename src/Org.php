<?php
namespace GravityLegal\GravityLegalAPI;
use DateTime;

class Org {
    public array $apps; // This is a list of App
    public string $id;
    public DateTime $createdOn;
    public ?DateTime $updatedOn;
    public bool $deleted;
    public string $name;
    public string $origin;
    public string $type;
    public ?AppData $appData;
}
